<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Form\Builder;

use A2lix\AutoFormBundle\Form\Attribute\AutoTypeCustom;
use A2lix\AutoFormBundle\Form\Type\AutoType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @phpstan-type ChildOptions array{
 *    child_type?: class-string,
 *    child_name?: string,
 *    child_excluded?: bool,
 *    child_embedded?: bool,
 *    child_groups?: list<string>,
 *    ...
 * }
 * @phpstan-type ChildBuilderCallable callable(FormBuilderInterface<mixed> $builder, ?array<string, mixed> $propAttributeOptions): FormBuilderInterface<mixed>
 * @phpstan-type FormBuilderCallable callable(FormBuilderInterface<mixed> $builder, list<string> $classProperties): void
 * @phpstan-type FormOptionsDefaults array{
 *    children: array<string, ChildOptions|ChildBuilderCallable>,
 *    children_excluded: list<string>|"*",
 *    children_embedded: list<string>|"*",
 *    children_groups: list<string>|null,
 *    builder: FormBuilderCallable|null,
 *    handle_translation_types: bool,
 *    gedmo_only: bool,
 * }
 */
final readonly class AutoTypeBuilder
{
    public function __construct(
        private PropertyInfoExtractorInterface $propertyInfoExtractor,
    ) {}

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param FormOptionsDefaults         $formOptions
     */
    public function buildChildren(FormBuilderInterface $builder, array $formOptions): void
    {
        $dataClass = $this->getDataClass($form = $builder->getForm());

        if (null === $classProperties = $this->propertyInfoExtractor->getProperties($dataClass)) {
            throw new \RuntimeException(\sprintf('Unable to extract properties of "%s".', $dataClass));
        }

        $refClass = new \ReflectionClass($dataClass);
        $allChildrenExcluded = '*' === $formOptions['children_excluded'];
        $allChildrenEmbedded = '*' === $formOptions['children_embedded'];
        $childrenGroups = $formOptions['children_groups'];
        $handleTranslationTypes = $formOptions['handle_translation_types'];
        $gedmoTranslatable = $handleTranslationTypes && (null !== ($refClass->getAttributes('Gedmo\Mapping\Annotation\TranslationEntity')[0] ?? null));
        $formDepth = $this->getFormDepth($form);

        /** @var list<string> $classProperties */
        foreach ($classProperties as $classProperty) {
            // Due to issue with DateTimeImmutable PHP8.4
            if (!$refClass->hasProperty($classProperty)) {
                continue;
            }

            $refProperty = $refClass->getProperty($classProperty);

            // Gedmo Translatable property? Possible continue early
            if ($gedmoTranslatable) {
                $hasGedmoAttribute = null !== ($refProperty->getAttributes('Gedmo\Mapping\Annotation\Translatable')[0] ?? null);

                if ($formOptions['gedmo_only'] xor $hasGedmoAttribute) {
                    unset($formOptions['children'][$classProperty]);
                    continue;
                }
            }

            $propFormOptions = $formOptions['children'][$classProperty] ?? null;
            $propAttributeOptions = ($refProperty->getAttributes(AutoTypeCustom::class)[0] ?? null)
                ?->newInstance()?->getOptions() ?? []
            ;

            // Custom name?
            if (null !== ($propAttributeOptions['child_name'] ?? null)) {
                $propAttributeOptions['property_path'] = $classProperty;
            }

            // FORM.children[PROP] callable? Add early
            if (\is_callable($propFormOptions)) {
                $childBuilder = ($propFormOptions)($builder, $propAttributeOptions);
                $this->addChild($builder, $childBuilder);
                unset($formOptions['children'][$classProperty]);
                continue;
            }

            /** @var ChildOptions */
            $childOptions = [
                ...$propAttributeOptions,
                ...($propFormOptions ?? []),
            ];

            // @phpstan-ignore argument.type
            $formChildExcluded = ((null === $propFormOptions) && ($allChildrenExcluded || \in_array($classProperty, $formOptions['children_excluded'], true)))
                || ($childOptions['child_excluded'] ?? false);

            // Excluded child? Continue early
            if ($formChildExcluded) {
                unset($formOptions['children'][$classProperty]);
                continue;
            }

            // Invalid matching group? Continue early
            $childGroups = $childOptions['child_groups'] ?? ['Default'];
            if ([] === array_intersect($childrenGroups, $childGroups)) {
                unset($formOptions['children'][$classProperty]);
                continue;
            }

            // PropertyInfo? Enrich childOptions
            if (null !== $propTypeInfo = $this->propertyInfoExtractor->getType($dataClass, $classProperty)) {
                $formChildTranslations = $handleTranslationTypes && ('translations' === $classProperty);
                // @phpstan-ignore argument.type
                $formChildEmbedded = $allChildrenEmbedded || \in_array($classProperty, $formOptions['children_embedded'], true)
                    || ($childOptions['child_embedded'] ?? false);

                $childOptions = match (true) {
                    $formChildTranslations => $this->updateTranslationsChildOptions($dataClass, $gedmoTranslatable, $childOptions),
                    $formChildEmbedded => $this->updateEmbeddedChildOptions($propTypeInfo, $childOptions, $formDepth, $refProperty),
                    default => $childOptions,
                };
            }

            $this->addChild($builder, $classProperty, $childOptions);
            unset($formOptions['children'][$classProperty]);
        }

        if ($formOptions['gedmo_only']) {
            return;
        }

        // Remaining FORM.children[PROP] unrelated to dataClass? E.g: mapped:false OR inherit_data:true
        foreach ($formOptions['children'] as $childProperty => $childOptions) {
            // FORM.children[PROP] callable? Continue early
            if (\is_callable($childOptions)) {
                $childBuilder = ($childOptions)($builder, null);
                $this->addChild($builder, $childBuilder);
                continue;
            }

            $this->addChild($builder, $childProperty, $childOptions);
        }

        // FORM.builder callable? Final modifications
        if (null !== $builderFn = $formOptions['builder']) {
            ($builderFn)($builder, $classProperties);
        }
    }

    /**
     * @param FormBuilderInterface<mixed>        $builder
     * @param string|FormBuilderInterface<mixed> $child
     * @param ChildOptions                       $options
     */
    private function addChild(FormBuilderInterface $builder, string|FormBuilderInterface $child, array $options = []): void
    {
        if ($child instanceof FormBuilderInterface) {
            $builder->add($child);

            return;
        }

        [
            'child_name' => $name,
            'child_type' => $type
        ] = $options + [
            'child_name' => $child,
            'child_type' => null,
        ];
        unset(
            $options['child_name'],
            $options['child_type'],
            $options['child_excluded'],
            $options['child_embedded'],
            $options['child_groups'],
        );

        $builder->add($name, $type, $options);
    }

    /**
     * @param FormInterface<mixed> $form
     *
     * @return class-string
     */
    private function getDataClass(FormInterface $form): string
    {
        do {
            if (null !== $dataClass = $form->getConfig()->getDataClass()) {
                /** @var class-string */
                return $dataClass;
            }
        } while (null !== $form = $form->getParent());

        throw new \RuntimeException('Unable to get dataClass');
    }
    /**
     * @param ChildOptions $baseChildOptions
     *
     * @return ChildOptions
     */
    private function updateTranslationsChildOptions(
        string $translatableClass,
        bool $gedmoTranslatable,
        array $baseChildOptions,
    ): array {
        return [
            'child_type' => 'A2lix\TranslationFormBundle\Form\Type\TranslationsType',
            'translatable_class' => $translatableClass,
            'gedmo' => $gedmoTranslatable,
            ...$baseChildOptions,
        ];
    }

    /**
     * @param ChildOptions $baseChildOptions
     *
     * @return ChildOptions
     */
    private function updateEmbeddedChildOptions(
        TypeInfo $propTypeInfo,
        array $baseChildOptions,
        int $formDepth,
        \ReflectionProperty $refProperty,
    ): array {
        // TypeInfo matching native FormType? Abort, guessers are enough
        if (self::isTypeInfoWithMatchingNativeFormType($propTypeInfo)) {
            return $baseChildOptions;
        }

        // Embeddable collection (object or builtin)?
        if ($propTypeInfo instanceof TypeInfo\CollectionType) {
            $baseCollOptions = [
                'child_type' => CollectionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'prototype_name' => '__name'.$formDepth.'__',
                ...$baseChildOptions,
            ];

            $collValueType = $propTypeInfo->getCollectionValueType();

            // Object?
            if ($collValueType instanceof TypeInfo\ObjectType) {
                return [
                    'entry_type' => AutoType::class,
                    ...$baseCollOptions,
                    'entry_options' => [
                        'data_class' => $collValueType->getClassName(),
                        // @phpstan-ignore nullCoalesce.offset
                        ...($baseCollOptions['entry_options'] ?? []),
                    ],
                ];
            }

            // Builtin
            return $baseCollOptions;
        }

        // Embeddable object
        /** @var TypeInfo\ObjectType<mixed> */
        $innerType = $propTypeInfo instanceof TypeInfo\NullableType ? $propTypeInfo->getWrappedType() : $propTypeInfo;

        if (Collection::class === $innerType->getClassName()) {
            throw new \RuntimeException(sprintf(
                'Unprecise PhpDoc Collection detected for "%s:%s". Fix it. For example: "@param Collection<int, Obj> $%s"',
                $refProperty->class,
                $refProperty->name,
                $refProperty->name,
            ));
        }

        return [
            'child_type' => AutoType::class,
            'data_class' => $innerType->getClassName(),
            'required' => $propTypeInfo->isNullable(),
            ...$baseChildOptions,
        ];
    }

    private static function isTypeInfoWithMatchingNativeFormType(TypeInfo $propTypeInfo): bool
    {
        // Array? Some native FormTypes with high confidence ('multiple' option) can match
        if ($propTypeInfo instanceof TypeInfo\CollectionType) {
            $collValueType = $propTypeInfo->getCollectionValueType();

            return $collValueType->isIdentifiedBy(\UnitEnum::class, \DateTimeZone::class);
        }

        // Builtin? Native FormType should fine
        if (!$propTypeInfo->isIdentifiedBy(TypeIdentifier::OBJECT)) {
            return true;
        }

        // Otherwise, some native FormTypes with high confidence can match
        return $propTypeInfo->isIdentifiedBy(
            \UnitEnum::class,
            \DateTime::class,
            \DateTimeImmutable::class,
            \DateInterval::class,
            \DateTimeZone::class,
            'Symfony\Component\Uid\Ulid',
            'Symfony\Component\Uid\Uuid',
            'Symfony\Component\HttpFoundation\File\File',
        );
    }

    /**
     * @param FormInterface<mixed> $form
     */
    private function getFormDepth(FormInterface $form): int
    {
        if ($form->isRoot()) {
            return 0;
        }

        $depth = 0;
        while (null !== $formParent = $form->getParent()) {
            $form = $formParent;
            ++$depth;
        }

        return $depth;
    }
}
