<?php

declare(strict_types=1);

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
use Symfony\Component\Form\FormInterface;
use A2lix\AutoFormBundle\Form\Type\AutoType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\TypeInfo\Type as TypeInfo;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @psalm-import-type formOptionsDefaults from AutoType
 * @psalm-import-type childOptions from AutoType
 */
class AutoTypeBuilder
{
    public function __construct(
        private readonly PropertyInfoExtractorInterface $propertyInfoExtractor,
    ) {}

    /**
     * @param formOptionsDefaults $formOptions
     */
    public function buildChildren(FormBuilderInterface $builder, array $formOptions): void
    {
        $dataClass = $this->getDataClass($builder->getForm());

        if (null === $classProperties = $this->propertyInfoExtractor->getProperties($dataClass)) {
            throw new \RuntimeException(sprintf('Unable to extract properties of "%s".', $dataClass));
        }

        $refClass = new \ReflectionClass($dataClass);
        $allChildrenExcluded = '*' === $formOptions['children_excluded'];
        $allChildrenEmbedded = '*' === $formOptions['children_embedded'];

        foreach ($classProperties as $classProperty) {
            // Issue: DateTimeImmutable PHP8.4
            if (!$refClass->hasProperty($classProperty)) {
                continue;
            }

            // if (!$this->propertyInfoExtractor->isWritable($dataClass, $classProperty)) {
            //     continue;
            // }

            $propFormOptions = $formOptions['children'][$classProperty] ?? null;

            $refProperty = $refClass->getProperty($classProperty);
            $propAttributeOptions = ($refProperty->getAttributes(AutoTypeCustom::class)[0] ?? null)
                ?->newInstance()?->getOptions() ?? [];

            // FORM.children[PROP] callable? Add early
            if (is_callable($propFormOptions)) {
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
                $formChildExcluded = $allChildrenExcluded || in_array($classProperty, $formOptions['children_excluded'], true)
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

            // classProperty.propertyInfo? Enrich childOptions
            if (null !== $propertyTypeInfo = $this->propertyInfoExtractor->getType($dataClass, $classProperty)) {
                /** @psalm-suppress RiskyTruthyFalsyComparison */
                /** @var list<string> $formOptions['children_embedded'] */
                $formChildEmbedded = $allChildrenEmbedded || in_array($classProperty, $formOptions['children_embedded'], true)
                    || ($propAttributeOptions['child_embedded'] ?? false);
                $childOptions = $this->updateChildOptions($childOptions, $propertyTypeInfo, $formChildEmbedded);
            }

            $this->addChild($builder, $classProperty, $childOptions);
            unset($formOptions['children'][$classProperty]);
        }

        // Remaining FORM.children[PROP] unrelated to dataClass? E.g: mapped:false OR inherit_data:true
        foreach ($formOptions['children'] as $childProperty => $childOptions) {
            // FORM.children[PROP] callable? Continue early
            if (is_callable($childOptions)) {
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
        if (null === $propertyTypeInfo = $this->propertyInfoExtractor->getType($class, $childName)) {
            throw new \RuntimeException(sprintf('Unable to find the association target class of "%s" in %s.', $childName, $class));
        }

        $innerType = $propertyTypeInfo instanceof TypeInfo\CollectionType ? $propertyTypeInfo->getCollectionValueType() : $propertyTypeInfo;
        if (!$innerType instanceof TypeInfo\ObjectType) {
            throw new \RuntimeException(sprintf('Unable to find the association target class of "%s" in %s.', $childName, $class));
        }

        return $innerType->getClassName();
    }

    private function updateChildOptions(array $baseChildOptions, TypeInfo $propertyTypeInfo, bool $formChildEmbedded): array
    {
        $isObject = $propertyTypeInfo->isIdentifiedBy(TypeIdentifier::OBJECT);

        if (!$isObject && !$propertyTypeInfo instanceof TypeInfo\CollectionType) {
            // TODO Enrich child_type & required?
            return $baseChildOptions;
        }

        if (!$formChildEmbedded) {
            return $baseChildOptions;
        }

        // Embeddable collection?
        if ($propertyTypeInfo instanceof TypeInfo\CollectionType) {
            $baseCollOptions =  [
                'child_type' => CollectionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                ...$baseChildOptions,
            ];

            $collValueType = $propertyTypeInfo->getCollectionValueType();

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
            // TODO Enrich entry_type?
            return $baseCollOptions;
        }

        // Embeddable object
        /** @var TypeInfo\ObjectType */
        $innerType = $propertyTypeInfo instanceof TypeInfo\NullableType ? $propertyTypeInfo->getWrappedType() : $propertyTypeInfo;

        return [
            'child_type' => AutoType::class,
            'data_class' => $innerType->getClassName(),
            'required' => $propertyTypeInfo->isNullable(),
            ...$baseChildOptions
        ];
    }
}
