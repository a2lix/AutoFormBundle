<?php

namespace A2lix\AutoFormBundle\Form\EventListener;

use A2lix\AutoFormBundle\Form\Manipulator\FormManipulatorInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 *
 */
class AutoFormListener implements EventSubscriberInterface
{
    /** @var FormManipulatorInterface */
    private $formManipulator;

    /**
     * @param FormManipulatorInterface $formManipulator
     */
    public function __construct(FormManipulatorInterface $formManipulator)
    {
        $this->formManipulator = $formManipulator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $formConfig = $form->getConfig();

        $fieldsClass = ClassUtils::getRealClass($formConfig->getDataClass());

        $fieldsOptions = $this->formManipulator->getFieldsConfig($fieldsClass, $formConfig->getOptions());
        foreach ($fieldsOptions as $fieldName => $fieldConfig) {
            $fieldType = isset($fieldConfig['field_type']) ? $fieldConfig['field_type'] : null;
            unset($fieldConfig['field_type']);

            $form->add($fieldName, $fieldType, $fieldConfig);
        }
    }
}
