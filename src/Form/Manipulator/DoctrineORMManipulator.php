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
use Doctrine\Common\Util\ClassUtils;
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
        $validObjectFieldsConfig = $this->filteringValidFields($objectFieldsConfig, $formOptions['excluded_fields']);

        if (empty($formOptions['fields'])) {
            return $validObjectFieldsConfig;
        }

        // Check unknows fields
        $unknowsFields = array_diff(array_keys($formOptions['fields']), array_keys($validObjectFieldsConfig));
        if (count($unknowsFields) > 0) {
            throw new \RuntimeException(sprintf("Field(s) '%s' doesn't exist in %s", implode(', ', $unknowsFields), $class));
        }

        foreach ($formOptions['fields'] as $formFieldName => $formFieldConfig) {
            if (null === $formFieldConfig) {
                continue;
            }

            // If display undesired, remove
            if (isset($formFieldConfig['display']) && (false === $formFieldConfig['display'])) {
                unset($validObjectFieldsConfig[$formFieldName]);
                continue;
            }

            // Override with formFieldsConfig priority
            $validObjectFieldsConfig[$formFieldName] = $formFieldConfig + $validObjectFieldsConfig[$formFieldName];
        }

        return $validObjectFieldsConfig;
    }

    private function getDataClass(FormInterface $form): string
    {
        // Simple case, data_class from current form
        if (null !== $dataClass = $form->getConfig()->getDataClass()) {
            return ClassUtils::getRealClass($dataClass);
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

    private function filteringValidFields(array $objectFieldsConfig, array $formExcludedFields): array
    {
        $excludedFields = array_merge($this->globalExcludedFields, $formExcludedFields);

        $validFields = [];
        foreach ($objectFieldsConfig as $fieldName => $fieldConfig) {
            if (in_array($fieldName, $excludedFields, true)) {
                continue;
            }

            $validFields[$fieldName] = $fieldConfig;
        }

        return $validFields;
    }
}
