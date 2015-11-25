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
    private $excludedFields;

    /**
     * @param ObjectInfoInterface $objectInfo
     * @param array               $excludedFields
     */
    public function __construct(ObjectInfoInterface $objectInfo, array $excludedFields = [])
    {
        $this->objectInfo = $objectInfo;
        $this->excludedFields = $excludedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldsConfig($class, array $formFieldsConfig)
    {
        $objectFieldsConfig = $this->objectInfo->getFieldsConfig($class);

        // Filtering to remove excludedFields
        $usuableObjectFieldsConfig = $this->filteringUsuableFields($objectFieldsConfig);

        if (empty($formFieldsConfig)) {
            return $usuableObjectFieldsConfig;
        }

        // Check unknows fields
        $unknowsFields = array_diff(array_keys($formFieldsConfig), array_keys($usuableObjectFieldsConfig));
        if (count($unknowsFields)) {
            throw new \RuntimeException(sptrinf("Field(s) '%s' doesn't exist in %s", implode(', ', $unknowsFields), $class));
        }

        foreach ($formFieldsConfig as $formFieldName => $formFieldConfig) {
            if (null === $formFieldConfig) {
                continue;
            }

            // If display undesired, remove
            if (isset($formFieldConfig['display']) && (false === $formFieldConfig['display'])) {
                unset($usuableObjectFieldsConfig[$formFieldName]);
            }

            // Override with formFieldsConfig priority
            $usuableObjectFieldsConfig[$formFieldName] = $formFieldConfig + $usuableObjectFieldsConfig[$formFieldName];
        }

        return $usuableObjectFieldsConfig;
    }

    /**
     * @param array $objectFieldsConfig
     *
     * @return array
     */
    private function filteringUsuableFields(array $objectFieldsConfig)
    {
        $usualableFields = [];

        foreach ($objectFieldsConfig as $fieldName => $fieldConfig) {
            if (!in_array($fieldName, $this->excludedFields, true)) {
                $usualableFields[$fieldName] = $fieldConfig;
            }
        }

        return $usualableFields;
    }
}
