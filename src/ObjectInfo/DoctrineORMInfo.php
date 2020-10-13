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

namespace A2lix\AutoFormBundle\ObjectInfo;

use A2lix\AutoFormBundle\Form\Type\AutoFormType;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class DoctrineORMInfo
{
    /** @var ClassMetadataFactory */
    private $classMetadataFactory;

    public function __construct(ClassMetadataFactory $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }

    public function getFieldsConfig(string $class): array
    {
        $fieldsConfig = [];

        $metadata = $this->classMetadataFactory->getMetadataFor($class);

        if (!empty($fields = $metadata->getFieldNames())) {
            $fieldsConfig = array_fill_keys($fields, []);
        }

        if (!empty($assocNames = $metadata->getAssociationNames())) {
            $fieldsConfig += $this->getAssocsConfig($metadata, $assocNames);
        }

        return $fieldsConfig;
    }

    public function getAssociationTargetClass(string $class, string $fieldName): string
    {
        $metadata = $this->classMetadataFactory->getMetadataFor($class);

        if (!$metadata->hasAssociation($fieldName)) {
            throw new \RuntimeException(sprintf('Unable to find the association target class of "%s" in %s.', $fieldName, $class));
        }

        return $metadata->getAssociationTargetClass($fieldName);
    }

    private function getAssocsConfig(ClassMetadata $metadata, array $assocNames): array
    {
        $assocsConfigs = [];

        foreach ($assocNames as $assocName) {
            if (!$metadata->isAssociationInverseSide($assocName)) {
                continue;
            }

            $class = $metadata->getAssociationTargetClass($assocName);

            if ($metadata->isSingleValuedAssociation($assocName)) {
                $nullable = ($metadata instanceof ClassMetadataInfo) && isset($metadata->discriminatorColumn['nullable']) && $metadata->discriminatorColumn['nullable'];

                $assocsConfigs[$assocName] = [
                    'field_type' => AutoFormType::class,
                    'data_class' => $class,
                    'required' => !$nullable,
                ];

                continue;
            }

            $assocsConfigs[$assocName] = [
                'field_type' => CollectionType::class,
                'entry_type' => AutoFormType::class,
                'entry_options' => [
                    'data_class' => $class,
                ],
                'allow_add' => true,
                'by_reference' => false,
            ];
        }

        return $assocsConfigs;
    }
}
