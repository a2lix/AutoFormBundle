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

use Symfony\Component\Form\FormInterface;

interface FormManipulatorInterface
{
    public function getFieldsConfig(FormInterface $form): array;
}
