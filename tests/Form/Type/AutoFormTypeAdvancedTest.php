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

namespace A2lix\AutoFormBundle\Tests\Form\Type;

use A2lix\AutoFormBundle\Form\Type\AutoFormType;
use A2lix\AutoFormBundle\Tests\Fixtures\Entity\Media;
use A2lix\AutoFormBundle\Tests\Fixtures\Entity\Product;
use A2lix\AutoFormBundle\Tests\Form\TypeTestCase;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\PreloadedExtension;

/**
 * @internal
 */
final class AutoFormTypeAdvancedTest extends TypeTestCase
{
    public function testCreationFormWithOverriddenFieldsLabel(): Product
    {
        $form = $this->factory->createBuilder(AutoFormType::class, new Product(), [
            'fields' => [
                'mainMedia' => [
                    'label' => 'Main Media',
                ],
                'url' => [
                    'label' => 'URL/URI',
                ],
            ],
        ])
            ->add('create', SubmitType::class)
            ->getForm()
        ;

        $media1 = new Media();
        $media1->setUrl('http://example.org/media1')
            ->setDescription('media1 desc')
        ;
        $media2 = new Media();
        $media2->setUrl('http://example.org/media2')
            ->setDescription('media2 desc')
        ;
        $media3 = new Media();
        $media3->setUrl('http://example.org/media3')
            ->setDescription('media3 desc')
        ;

        $product = new Product();
        $product
            ->setUrl('a2lix.fr')
            ->setMainMedia($media3)
            ->addMedia($media1)
            ->addMedia($media2)
        ;

        $formData = [
            'url' => 'a2lix.fr',
            'mainMedia' => [
                'url' => 'http://example.org/media3',
                'description' => 'media3 desc',
            ],
            'medias' => [
                [
                    'url' => 'http://example.org/media1',
                    'description' => 'media1 desc',
                ],
                [
                    'url' => 'http://example.org/media2',
                    'description' => 'media2 desc',
                ],
            ],
        ];

        $form->submit($formData);
        static::assertTrue($form->isSynchronized());
        static::assertEquals($product, $form->getData());
        static::assertEquals('URL/URI', $form->get('url')->getConfig()->getOptions()['label']);

        return $product;
    }

    public function testCreationFormWithOverriddenFieldsMappedFalse(): Product
    {
        $form = $this->factory->createBuilder(AutoFormType::class, new Product(), [
            'fields' => [
                'color' => [
                    'mapped' => false,
                ],
            ],
        ])
            ->add('create', SubmitType::class)
            ->getForm()
        ;

        $media1 = new Media();
        $media1->setUrl('http://example.org/media1')
            ->setDescription('media1 desc')
        ;
        $media2 = new Media();
        $media2->setUrl('http://example.org/media2')
            ->setDescription('media2 desc')
        ;

        $product = new Product();
        $product->setUrl('a2lix.fr')
            ->addMedia($media1)
            ->addMedia($media2)
        ;

        $formData = [
            'url' => 'a2lix.fr',
            'color' => 'blue',
            'medias' => [
                [
                    'url' => 'http://example.org/media1',
                    'description' => 'media1 desc',
                ],
                [
                    'url' => 'http://example.org/media2',
                    'description' => 'media2 desc',
                ],
            ],
        ];

        $form->submit($formData);
        static::assertTrue($form->isSynchronized());
        static::assertEquals($product, $form->getData());
        static::assertEquals('blue', $form->get('color')->getData());

        return $product;
    }

    protected function getExtensions(): array
    {
        $autoFormType = $this->getConfiguredAutoFormType();

        return [new PreloadedExtension([
            $autoFormType,
        ], [])];
    }
}
