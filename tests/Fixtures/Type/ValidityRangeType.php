<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Tests\Fixtures\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class ValidityRangeType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('validityStartAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'attr' => ['class' => 'form-grid'],
            ])
            ->add('validityEndAt', DateTimeType::class, [
                'input' => 'datetime_immutable',
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'attr' => ['class' => 'form-grid'],
            ])
        ;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'inherit_data' => true,
        ]);
    }
}
