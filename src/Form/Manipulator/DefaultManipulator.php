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

use A2lix\AutoFormBundle\ObjectInfo\ObjectInfoInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Form\FormInterface;

class DefaultManipulator implements FormManipulatorInterface
{
    private $objectInfo;
    private $globalExcludedFields;

    public function __construct(ObjectInfoInterface $objectInfo, array $globalExcludedFields = [])
    {
        $this->objectInfo = $objectInfo;
        $this->globalExcludedFields = $globalExcludedFields;
    }

    public function getFieldsConfig(FormInterface $form): array
    {
        $class = $this->getDataClass($form);
        $formOptions = $form->getConfig()->getOptions();

        // Filtering to remove excludedFields
        $objectFieldsConfig = $this->objectInfo->getFieldsConfig($class);
        $usuableObjectFieldsConfig = $this->filteringUsuableFields($objectFieldsConfig, $formOptions['excluded_fields']);

        if (empty($formOptions['fields'])) {
            return $usuableObjectFieldsConfig;
        }

        // Check unknows fields
        $unknowsFields = array_diff(array_keys($formOptions['fields']), array_keys($usuableObjectFieldsConfig));
        if (count($unknowsFields)) {
            throw new \RuntimeException(sprintf("Field(s) '%s' doesn't exist in %s", implode(', ', $unknowsFields), $class));
        }

        foreach ($formOptions['fields'] as $formFieldName => $formFieldConfig) {
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

    private function getDataClass(FormInterface $form): string
    {
        // Simple case, data_class from current form
        if ($dataClass = $form->getConfig()->getDataClass()) {
            return ClassUtils::getRealClass($dataClass);
        }

        // Advanced case, loop parent form to get closest fill data_class
        while ($formParent = $form->getParent()) {
            if (!$dataClass = $formParent->getConfig()->getDataClass()) {
                $form = $formParent;
                continue;
            }

            return $this->objectInfo->getAssociationTargetClass($dataClass, $form->getName());
        }
    }

    private function filteringUsuableFields(array $objectFieldsConfig, array $formExcludedFields): array
    {
        $excludedFields = array_merge($this->globalExcludedFields, $formExcludedFields);

        $usualableFields = [];
        foreach ($objectFieldsConfig as $fieldName => $fieldConfig) {
            if (in_array($fieldName, $excludedFields, true)) {
                continue;
            }

            $usualableFields[$fieldName] = $fieldConfig;
        }

        return $usualableFields;
    }
}
