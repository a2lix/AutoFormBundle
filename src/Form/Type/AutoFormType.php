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

namespace A2lix\AutoFormBundle\Form\Type;

use A2lix\AutoFormBundle\Form\EventListener\AutoFormListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutoFormType extends AbstractType
{
    /** @var AutoFormListener */
    private $autoFormListener;

    public function __construct(AutoFormListener $autoFormListener)
    {
        $this->autoFormListener = $autoFormListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber($this->autoFormListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'fields' => [],
            'excluded_fields' => [],
        ]);

        $resolver->setNormalizer('data_class', function (Options $options, $value): string {
            if (empty($value)) {
                throw new \RuntimeException('Missing "data_class" option of "AutoFormType".');
            }

            return $value;
        });
    }
}
