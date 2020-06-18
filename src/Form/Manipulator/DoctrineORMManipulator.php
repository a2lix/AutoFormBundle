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

namespace A2lix\AutoFormBundle\Form\Manipulator;

use A2lix\AutoFormBundle\ObjectInfo\DoctrineORMInfo;
use Symfony\Component\Form\FormInterface;

class DoctrineORMManipulator implements FormManipulatorInterface
{
    /** @var DoctrineORMInfo */
    private $doctrineORMInfo;
    /** @var array */
    private $globalExcludedFields;

    public function __construct(DoctrineORMInfo $doctrineORMInfo, array $globalExcludedFields = [])
    {
        $this->doctrineORMInfo = $doctrineORMInfo;
        $this->globalExcludedFields = $globalExcludedFields;
    }

    public function getFieldsConfig(FormInterface $form): array
    {
        $class = $this->getDataClass($form);
        $formOptions = $form->getConfig()->getOptions();

        // Filtering to remove excludedFields
        $objectFieldsConfig = $this->doctrineORMInfo->getFieldsConfig($class);
        $validObjectFieldsConfig = $this->filteringValidObjectFields($objectFieldsConfig, $formOptions['excluded_fields']);

        if (empty($formOptions['fields'])) {
            return $validObjectFieldsConfig;
        }

        // Check correctness of remaining fields
        $unmappedFieldsConfig = $this->filteringValidRemainingFields($validObjectFieldsConfig, $formOptions['fields'], $class);

        foreach ($formOptions['fields'] as $formFieldName => $formFieldConfig) {
            if (isset($unmappedFieldsConfig[$formFieldName])) {
                continue;
            }

            if (null === $formFieldConfig) {
                continue;
            }

            // If display undesired, remove
            if (false === ($formFieldConfig['display'] ?? true)) {
                unset($validObjectFieldsConfig[$formFieldName]);
                continue;
            }

            // Override with formFieldsConfig priority
            $validObjectFieldsConfig[$formFieldName] = $formFieldConfig + $validObjectFieldsConfig[$formFieldName];
        }

        return $validObjectFieldsConfig + $unmappedFieldsConfig;
    }

    private function getDataClass(FormInterface $form): string
    {
        // Simple case, data_class from current form (with ORM Proxy management)
        if (null !== $dataClass = $form->getConfig()->getDataClass()) {
            if (false === $pos = strrpos($dataClass, '\\__CG__\\')) {
                return $dataClass;
            }

            return substr($dataClass, $pos + 8);
        }

        // Advanced case, loop parent form to get closest fill data_class
        while (null !== $formParent = $form->getParent()) {
            if (null === $dataClass = $formParent->getConfig()->getDataClass()) {
                $form = $formParent;
                continue;
            }

            return $this->doctrineORMInfo->getAssociationTargetClass($dataClass, $form->getName());
        }

        throw new \RuntimeException('Unable to get dataClass');
    }

    private function filteringValidObjectFields(array $objectFieldsConfig, array $formExcludedFields): array
    {
        $excludedFields = array_merge($this->globalExcludedFields, $formExcludedFields);

        $validFields = [];
        foreach ($objectFieldsConfig as $fieldName => $fieldConfig) {
            if (\in_array($fieldName, $excludedFields, true)) {
                continue;
            }

            $validFields[$fieldName] = $fieldConfig;
        }

        return $validFields;
    }

    private function filteringValidRemainingFields(array $validObjectFieldsConfig, array $formFields, string $class): array
    {
        $unmappedFieldsConfig = [];

        $validObjectFieldsKeys = array_keys($validObjectFieldsConfig);
        $unknowsFields = [];

        foreach ($formFields as $fieldName => $fieldConfig) {
            if (\in_array($fieldName, $validObjectFieldsKeys, true)) {
                continue;
            }

            if (false === ($fieldConfig['mapped'] ?? true)) {
                $unmappedFieldsConfig[$fieldName] = $fieldConfig;
                continue;
            }

            $unknowsFields[] = $fieldName;
        }

        if (\count($unknowsFields) > 0) {
            throw new \RuntimeException(sprintf("Field(s) '%s' doesn't exist in %s", implode(', ', $unknowsFields), $class));
        }

        return $unmappedFieldsConfig;
    }
}
