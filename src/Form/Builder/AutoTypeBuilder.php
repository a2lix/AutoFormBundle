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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @psalm-import-type FormOptionsDefaults from AutoType
 */
class AutoTypeBuilder
{
    public function __construct(
        private readonly PropertyInfoExtractorInterface $propertyInfoExtractor,
    ) {}

    /**
     * @param FormOptionsDefaults $formOptions
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
        $formLevel = $this->getFormLevel($form);

        foreach ($classProperties as $classProperty) {
            // Due to issue with DateTimeImmutable PHP8.4
            if (!$refClass->hasProperty($classProperty)) {
                continue;
            }

            $propFormOptions = $formOptions['children'][$classProperty] ?? null;

            $refProperty = $refClass->getProperty($classProperty);
            $propAttributeOptions = ($refProperty->getAttributes(AutoTypeCustom::class)[0] ?? null)
                ?->newInstance()?->getOptions() ?? []
            ;

            // FORM.children[PROP] callable? Add early
            if (\is_callable($propFormOptions)) {
                /** @var FormBuilderInterface */
                $childBuilder = ($propFormOptions)($builder, $propAttributeOptions);
                $this->addChild($builder, $childBuilder);
                unset($formOptions['children'][$classProperty]);
                continue;
            }

            // FORM.children[PROP].child_excluded? Continue early
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if ($propFormOptions['child_excluded'] ?? false) {
                unset($formOptions['children'][$classProperty]);
                continue;
            }

            if (null === $propFormOptions) {
                /** @var list<string> $formOptions['children_excluded'] */
                $formChildExcluded = $allChildrenExcluded || \in_array($classProperty, $formOptions['children_excluded'], true)
                    || ($propAttributeOptions['child_excluded'] ?? false);

                // Excluded at form or attribute level? Continue early
                if ($formChildExcluded) {
                    unset($formOptions['children'][$classProperty]);
                    continue;
                }
            }

            $childOptions = [
                ...($propFormOptions ?? []),
                ...$propAttributeOptions,
            ];

            // PropertyInfo? Enrich childOptions
            if (null !== $propTypeInfo = $this->propertyInfoExtractor->getType($dataClass, $classProperty)) {
                /** @psalm-suppress RiskyTruthyFalsyComparison */
                /** @var list<string> $formOptions['children_embedded'] */
                $formChildEmbedded = $allChildrenEmbedded || \in_array($classProperty, $formOptions['children_embedded'], true)
                    || ($propAttributeOptions['child_embedded'] ?? false);

                if ($formChildEmbedded) {
                    $childOptions = $this->updateChildOptions($childOptions, $propTypeInfo, $formLevel);
                }
            }

            $this->addChild($builder, $classProperty, $childOptions);
            unset($formOptions['children'][$classProperty]);
        }

        // Remaining FORM.children[PROP] unrelated to dataClass? E.g: mapped:false OR inherit_data:true
        foreach ($formOptions['children'] as $childProperty => $childOptions) {
            // FORM.children[PROP] callable? Continue early
            if (\is_callable($childOptions)) {
                /** @var FormBuilderInterface */
                $childBuilder = ($childOptions)($builder);
                $this->addChild($builder, $childBuilder);
                continue;
            }

            /** @var string $childProperty */
            $this->addChild($builder, $childProperty, $childOptions);
        }

        // FORM.builder callable? Final modifications
        if (null !== $builderFn = $formOptions['builder']) {
            ($builderFn)($builder, $classProperties);
        }
    }

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
        unset($options['child_name'], $options['child_type'], $options['child_excluded'], $options['child_embedded']);

        /** @var string $name */
        /** @var class-string|null $type */
        /** @var array<string, mixed> $options */
        $builder->add($name, $type, $options);
    }

    /**
     * @return class-string
     */
    private function getDataClass(FormInterface $form): string
    {
        // Form data_class config? (With old proxy handling)
        if (null !== $dataClass = $form->getConfig()->getDataClass()) {
            if (false !== $pos = strrpos($dataClass, '\\__CG__\\')) {
                /** @var class-string */
                return substr($dataClass, $pos + 8);
            }

            /** @var class-string */
            return $dataClass;
        }

        // Loop parent form to get closest data_class config
        while (null !== $formParent = $form->getParent()) {
            if (null === $dataClass = $formParent->getConfig()->getDataClass()) {
                $form = $formParent;

                continue;
            }

            return $this->getAssociationTargetClass($dataClass, (string) $form->getPropertyPath());
        }

        throw new \RuntimeException('Unable to get dataClass');
    }

    /**
     * @return class-string
     */
    private function getAssociationTargetClass(string $class, string $childName): string
    {
        if (null === $propTypeInfo = $this->propertyInfoExtractor->getType($class, $childName)) {
            throw new \RuntimeException(\sprintf('Unable to find the association target class of "%s" in %s.', $childName, $class));
        }

        $innerType = $propTypeInfo instanceof TypeInfo\CollectionType ? $propTypeInfo->getCollectionValueType() : $propTypeInfo;
        if (!$innerType instanceof TypeInfo\ObjectType) {
            throw new \RuntimeException(\sprintf('Unable to find the association target class of "%s" in %s.', $childName, $class));
        }

        return $innerType->getClassName();
    }

    private function updateChildOptions(array $baseChildOptions, TypeInfo $propTypeInfo, int $formLevel): array
    {
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
                'prototype_name' => '__name'.$formLevel.'__',
                ...$baseChildOptions,
            ];

            $collValueType = $propTypeInfo->getCollectionValueType();

            // Object?
            if ($collValueType instanceof TypeInfo\ObjectType) {
                /** @psalm-suppress InvalidOperand */
                return [
                    'entry_type' => AutoType::class,
                    ...$baseCollOptions,
                    'entry_options' => [
                        'data_class' => $collValueType->getClassName(),
                        ...($baseCollOptions['entry_options'] ?? []),
                    ],
                ];
            }

            // Builtin
            return $baseCollOptions;
        }

        // Embeddable object
        /** @var TypeInfo\ObjectType */
        $innerType = $propTypeInfo instanceof TypeInfo\NullableType ? $propTypeInfo->getWrappedType() : $propTypeInfo;

        return [
            'child_type' => AutoType::class,
            'data_class' => $innerType->getClassName(),
            'required' => $propTypeInfo->isNullable(),
            ...$baseChildOptions,
        ];
    }

    private static function isTypeInfoWithMatchingNativeFormType(TypeInfo $propTypeInfo): bool
    {
        // Array? Some can match a native FormType with high confidence ('multiple' option)
        if ($propTypeInfo->isIdentifiedBy(TypeIdentifier::ARRAY)) {
            return $propTypeInfo instanceof TypeInfo\GenericType
                && $propTypeInfo->getVariableTypes()[1]->isIdentifiedBy(\UnitEnum::class, \DateTimeZone::class);
        }

        // Builtin? Native FormType will be ok.
        if (!$propTypeInfo->isIdentifiedBy(TypeIdentifier::OBJECT)) {
            return true;
        }

        // Some objects with high confidence FormType
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

    private function getFormLevel(FormInterface $form): int
    {
        if ($form->isRoot()) {
            return 0;
        }

        $level = 0;
        while (null !== $formParent = $form->getParent()) {
            $form = $formParent;
            ++$level;
        }

        return $level;
    }
}
