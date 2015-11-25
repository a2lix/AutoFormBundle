<?php

namespace A2lix\AutoFormBundle\Tests\Form\Type;

use A2lix\AutoFormBundle\Tests\Fixtures\Entity\Media;
use A2lix\AutoFormBundle\Tests\Fixtures\Entity\Product;
use A2lix\AutoFormBundle\Tests\Form\TypeTestCase;
use Symfony\Component\Form\PreloadedExtension;

/**
 * @author David ALLIX
 */
class AutoFormTypeTest extends TypeTestCase
{
    protected function getExtensions()
    {
        $autoFormType = $this->getConfiguredAutoFormType();

        return [new PreloadedExtension([
            $autoFormType,
        ], [])];
    }

    public function testSubmitValidDefaultConfigurationData()
    {
        // Creation
        $media1 = new Media();
        $media1->setUrl('http://example.org/media1')
               ->setDescription('media1 desc');
        $media2 = new Media();
        $media2->setUrl('http://example.org/media2')
               ->setDescription('media2 desc');

        $product = new Product();
        $product->setUrl('a2lix.fr')
                ->addMedia($media1)
                ->addMedia($media2);

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

        $form = $this->factory->createBuilder('A2lix\AutoFormBundle\Form\Type\AutoFormType', new Product())
            ->add('create', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->getForm();

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($product, $form->getData());

        // Edition
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

        $form = $this->factory->createBuilder('A2lix\AutoFormBundle\Form\Type\AutoFormType', new Product())
            ->add('create', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->getForm();

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($product, $form->getData());

        $view = $form->createView();
        $children = $view->children;

        foreach (array_keys($formData) as $key) {
            $this->assertArrayHasKey($key, $children);
        }
    }
}
