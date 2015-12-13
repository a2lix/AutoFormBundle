<?php

namespace A2lix\AutoFormBundle\Form\Manipulator;

/**
 * @author David ALLIX
 */
interface FormManipulatorInterface
{
    /**
     * @param string $class
     * @param array  $formConfig
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function getFieldsConfig($class, array $formConfig);
}
