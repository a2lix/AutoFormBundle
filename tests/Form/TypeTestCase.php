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

namespace A2lix\AutoFormBundle\Tests\Form;

use A2lix\AutoFormBundle\Form\EventListener\AutoFormListener;
use A2lix\AutoFormBundle\Form\Manipulator\DoctrineORMManipulator;
use A2lix\AutoFormBundle\Form\Type\AutoFormType;
use A2lix\AutoFormBundle\ObjectInfo\DoctrineORMInfo;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase as BaseTypeTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class TypeTestCase extends BaseTypeTestCase
{
    protected $doctrineORMManipulator;

    protected function setUp(): void
    {
        parent::setUp();

        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->will($this->returnValue(new ConstraintViolationList()));

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(
                new FormTypeValidatorExtension($validator)
            )
            ->addTypeGuesser(
                $this->getMockBuilder(ValidatorTypeGuesser::class)
                     ->disableOriginalConstructor()
                     ->getMock()
            )
            ->getFormFactory();

        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    protected function getDoctrineORMManipulator(): DoctrineORMManipulator
    {
        if (null !== $this->doctrineORMManipulator) {
            return $this->doctrineORMManipulator;
        }

        $config = Setup::createAnnotationMetadataConfiguration([__DIR__.'/../Fixtures/Entity'], true, null, null, false);
        $entityManager = EntityManager::create(['driver' => 'pdo_sqlite'], $config);
        $doctrineORMInfo = new DoctrineORMInfo($entityManager->getMetadataFactory());

        return $this->doctrineORMManipulator = new DoctrineORMManipulator($doctrineORMInfo, ['id', 'locale', 'translatable']);
    }

    protected function getConfiguredAutoFormType(): AutoFormType
    {
        $autoFormListener = new AutoFormListener($this->getDoctrineORMManipulator());

        return new AutoFormType($autoFormListener);
    }
}
