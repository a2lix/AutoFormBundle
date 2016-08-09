<?php

namespace A2lix\AutoFormBundle\Form\Manipulator;

use Symfony\Component\Form\FormInterface;

/**
 * @author David ALLIX
 */
interface FormManipulatorInterface
{
    /**
     * @param FormInterface $form
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function getFieldsConfig(FormInterface $form);
}
