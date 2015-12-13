<?php

namespace A2lix\AutoFormBundle\Form\Manipulator;

use A2lix\AutoFormBundle\ObjectInfo\ObjectInfoInterface;

/**
 * @author David ALLIX
 */
class DefaultManipulator implements FormManipulatorInterface
{
    /** @var ObjectInfoInterface */
    private $objectInfo;
    /** @var array */
    private $globalExcludedFields;

    /**
     * @param ObjectInfoInterface $objectInfo
     * @param array               $globalExcludedFields
     */
    public function __construct(ObjectInfoInterface $objectInfo, array $globalExcludedFields = [])
    {
        $this->objectInfo = $objectInfo;
        $this->globalExcludedFields = $globalExcludedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldsConfig($class, array $formConfig)
    {
        // Filtering to remove excludedFields
        $objectFieldsConfig = $this->objectInfo->getFieldsConfig($class);
        $usuableObjectFieldsConfig = $this->filteringUsuableFields($objectFieldsConfig, $formConfig['excluded_fields']);

        if (empty($formConfig['fields'])) {
            return $usuableObjectFieldsConfig;
        }

        // Check unknows fields
        $unknowsFields = array_diff(array_keys($formConfig['fields']), array_keys($usuableObjectFieldsConfig));
        if (count($unknowsFields)) {
            throw new \RuntimeException(sprintf("Field(s) '%s' doesn't exist in %s", implode(', ', $unknowsFields), $class));
        }

        foreach ($formConfig['fields'] as $formFieldName => $formFieldConfig) {
            if (null === $formFieldConfig) {
                continue;
            }

            // If display undesired, remove
            if (isset($formFieldConfig['display']) && (false === $formFieldConfig['display'])) {
                unset($usuableObjectFieldsConfig[$formFieldName]);
                continue;
            }

            // Override with formFieldsConfig priority
            $usuableObjectFieldsConfig[$formFieldName] = $formFieldConfig + $usuableObjectFieldsConfig[$formFieldName];
        }

        return $usuableObjectFieldsConfig;
    }

    /**
     * @param array $objectFieldsConfig
     * @param array $formExcludedFields
     *
     * @return array
     */
    private function filteringUsuableFields(array $objectFieldsConfig, array $formExcludedFields)
    {
        $excludedFields = array_merge($this->globalExcludedFields, $formExcludedFields);

        $usualableFields = [];
        foreach ($objectFieldsConfig as $fieldName => $fieldConfig) {
            if (!in_array($fieldName, $excludedFields, true)) {
                $usualableFields[$fieldName] = $fieldConfig;
            }
        }

        return $usualableFields;
    }
}
