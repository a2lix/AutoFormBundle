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

namespace A2lix\AutoFormBundle\Form\EventListener;

use A2lix\AutoFormBundle\Form\Manipulator\FormManipulatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AutoFormListener implements EventSubscriberInterface
{
    /** @var FormManipulatorInterface */
    private $formManipulator;

    public function __construct(FormManipulatorInterface $formManipulator)
    {
        $this->formManipulator = $formManipulator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    public function preSetData(FormEvent $event): void
    {
        $form = $event->getForm();

        $fieldsOptions = $this->formManipulator->getFieldsConfig($form);
        foreach ($fieldsOptions as $fieldName => $fieldConfig) {
            $fieldType = $fieldConfig['field_type'] ?? null;
            unset($fieldConfig['field_type']);

            $form->add($fieldName, $fieldType, $fieldConfig);
        }
    }
}
