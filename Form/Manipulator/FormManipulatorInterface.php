<?php

/*
 * This file is part of A2lix projects.
 *
 * (c) David ALLIX
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Form\Manipulator;

use Symfony\Component\Form\FormInterface;

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
