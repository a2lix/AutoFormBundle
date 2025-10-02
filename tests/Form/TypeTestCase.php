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

use A2lix\AutoFormBundle\Form\Builder\AutoTypeBuilder;
use A2lix\AutoFormBundle\Form\Type\AutoType;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Symfony\Bridge\Doctrine\PropertyInfo\DoctrineExtractor;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\Traits\ValidatorExtensionTrait;
use Symfony\Component\Form\Test\TypeTestCase as BaseTypeTestCase;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

abstract class TypeTestCase extends BaseTypeTestCase
{
    use ValidatorExtensionTrait;

    protected ?AutoTypeBuilder $autoTypeBuilder = null;

    protected function getConfiguredAutoType(array $childrenExcluded = []): AutoType
    {
        return new AutoType($this->getAutoTypeBuilder(), $childrenExcluded);
    }

    private function getAutoTypeBuilder(): AutoTypeBuilder
    {
        if (null !== $this->autoTypeBuilder) {
            return $this->autoTypeBuilder;
        }

        return $this->autoTypeBuilder = new AutoTypeBuilder(
            $this->getPropertyInfoExtractor(),
        );
    }

    private function getPropertyInfoExtractor(): PropertyInfoExtractor
    {
        $config = ORMSetup::createAttributeMetadataConfig([__DIR__ . '/../Fixtures/Entity'], true);
        $config->setProxyDir(__DIR__ . '/../proxies');
        $config->setProxyNamespace('EntityProxy');

        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $config);
        $entityManager = new EntityManager($connection, $config);

        $doctrineExtractor = new DoctrineExtractor($entityManager);
        $reflectionExtractor = new ReflectionExtractor();

        return new PropertyInfoExtractor(
            listExtractors: [
                $reflectionExtractor,
                $doctrineExtractor
            ],
            typeExtractors:[
                $doctrineExtractor,
                new PhpStanExtractor(),
                new PhpDocExtractor(),
                $reflectionExtractor
            ],
            accessExtractors: [
                $doctrineExtractor,
                $reflectionExtractor
            ]
        );
    }

    protected function getExtensions(): array
    {
        $autoType = $this->getConfiguredAutoType(['id']);

        return [
            ...parent::getExtensions(),
            new PreloadedExtension([$autoType], []),
        ];
    }
}
