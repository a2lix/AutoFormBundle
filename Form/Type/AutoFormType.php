<?php

namespace A2lix\AutoFormBundle\Form\Type;

use A2lix\AutoFormBundle\Form\EventListener\AutoFormListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author David ALLIX
 */
class AutoFormType extends AbstractType
{
    /** @var AutoFormListener */
    private $AutoFormListener;

    /**
     * @param AutoFormListener $AutoFormListener
     */
    public function __construct(AutoFormListener $AutoFormListener)
    {
        $this->AutoFormListener = $AutoFormListener;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->AutoFormListener);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'fields' => [],
        ]);
    }
}
