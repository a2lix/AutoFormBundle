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
final class AutoFormTypeSimpleTest extends TypeTestCase
{
    public function testEmptyForm(): void
    {
        $form = $this->factory->createBuilder(AutoFormType::class, new Product())
            ->add('create', SubmitType::class)
            ->getForm()
        ;

        static::assertEquals(['create', 'title', 'description', 'url', 'mainMedia', 'medias'], array_keys($form->all()), 'Fields should matches Product fields');

        $mediasFormOptions = $form->get('medias')->getConfig()->getOptions();
        static::assertEquals(AutoFormType::class, $mediasFormOptions['entry_type'], 'Media type should be an AutoType');
        static::assertEquals(Media::class, $mediasFormOptions['entry_options']['data_class'], 'Media should have its right data_class');
    }

    public function testCreationForm(): Product
    {
        $form = $this->factory->createBuilder(AutoFormType::class, new Product())
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

        return $product;
    }

    /**
     * @depends testCreationForm
     */
    public function testEditionForm(Product $product): void
    {
        $product->getMedias()[0]->setUrl('http://example.org/media1-edit');
        $product->getMedias()[1]->setDescription('media2 desc edit');

        $formData = [
            'url' => 'a2lix.fr',
            'medias' => [
                [
                    'url' => 'http://example.org/media1-edit',
                    'description' => 'media1 desc',
                ],
                [
                    'url' => 'http://example.org/media2',
                    'description' => 'media2 desc edit',
                ],
            ],
        ];

        $form = $this->factory->createBuilder(AutoFormType::class, new Product())
            ->add('create', SubmitType::class)
            ->getForm()
        ;

        $form->submit($formData);
        static::assertTrue($form->isSynchronized());
        static::assertEquals($product, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            static::assertArrayHasKey($key, $children);
        }
    }

    protected function getExtensions(): array
    {
        $autoFormType = $this->getConfiguredAutoFormType();

        return [new PreloadedExtension([
            $autoFormType,
        ], [])];
    }
}
