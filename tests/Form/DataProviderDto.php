<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Tests\Form;

use A2lix\AutoFormBundle\Form\Type\AutoType;
use A2lix\AutoFormBundle\Tests\Fixtures\Dto\Media1;
use A2lix\AutoFormBundle\Tests\Fixtures\Dto\Product1;
use A2lix\AutoFormBundle\Tests\Fixtures\ProductStatus;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormBuilderInterface;

final class DataProviderDto
{
    /**
     * @return \Iterator<array<int, TestScenario>>
     */
    public static function provideScenarioCases(): iterable
    {
        yield 'Dto - Product1 with default behavior, no options' => [
            new TestScenario(
                obj: new Product1(),
                expectedForm: [
                    'title' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => CoreType\IntegerType::class,
                    ],
                    'tags' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'mediaMain' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'mediaColl' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'status' => [
                        'expected_type' => CoreType\EnumType::class,
                        'class' => ProductStatus::class,
                    ],
                    'statusList' => [
                        'expected_type' => CoreType\EnumType::class,
                        'multiple' => true,
                        'class' => ProductStatus::class,
                    ],
                    'validityStartAt' => [
                        'expected_type' => CoreType\DateTimeType::class,
                        'input' => 'datetime_immutable',
                    ],
                    'validityEndAt' => [
                        'expected_type' => CoreType\DateTimeType::class,
                        'input' => 'datetime_immutable',
                    ],
                    'desc' => [
                        'expected_type' => CoreType\TextareaType::class,
                        'attr' => [
                            'rows' => 2,
                        ],
                        'property_path' => 'description',
                    ],
                ],
            ),
        ];

        yield 'Dto - Product1 with children_embedded = *' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_embedded' => '*',
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => CoreType\IntegerType::class,
                    ],
                    'tags' => [
                        'expected_type' => CoreType\CollectionType::class,
                        'entry_type' => CoreType\TextType::class,
                        'entry_options' => [
                            'block_name' => 'entry',
                        ],
                    ],
                    'mediaMain' => [
                        'expected_type' => AutoType::class,
                        'expected_children' => [
                            'url' => [
                                'expected_type' => CoreType\TextType::class,
                                'help' => 'media.url_help',
                            ],
                            'description' => [
                                'expected_type' => CoreType\TextareaType::class,
                            ],
                        ],
                    ],
                    'mediaColl' => [
                        'expected_type' => CoreType\CollectionType::class,
                        'entry_type' => AutoType::class,
                        'entry_options' => [
                            'data_class' => Media1::class,
                            'block_name' => 'entry',
                        ],
                    ],
                    'status' => [
                        'expected_type' => CoreType\EnumType::class,
                        'class' => ProductStatus::class,
                    ],
                    'statusList' => [
                        'expected_type' => CoreType\EnumType::class,
                        'multiple' => true,
                        'class' => ProductStatus::class,
                    ],
                    'validityStartAt' => [
                        'expected_type' => CoreType\DateTimeType::class,
                        'input' => 'datetime_immutable',
                    ],
                    'validityEndAt' => [
                        'expected_type' => CoreType\DateTimeType::class,
                        'input' => 'datetime_immutable',
                    ],
                    'desc' => [
                        'expected_type' => CoreType\TextareaType::class,
                        'attr' => [
                            'rows' => 2,
                        ],
                        'property_path' => 'description',
                    ],
                ],
            ),
        ];

        yield 'Dto - Product1 with children_embedded & child_embedded' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_embedded' => ['mediaColl'],
                    'children' => [
                        'tags' => [
                            'child_embedded' => true,
                        ],
                    ],
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => CoreType\IntegerType::class,
                    ],
                    'tags' => [
                        'expected_type' => CoreType\CollectionType::class,
                        'entry_type' => CoreType\TextType::class,
                        'entry_options' => [
                            'block_name' => 'entry',
                        ],
                    ],
                    'mediaMain' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'mediaColl' => [
                        'expected_type' => CoreType\CollectionType::class,
                        'entry_options' => [
                            'data_class' => Media1::class,
                            'block_name' => 'entry',
                        ],
                    ],
                    'status' => [
                        'expected_type' => CoreType\EnumType::class,
                        'class' => ProductStatus::class,
                    ],
                    'statusList' => [
                        'expected_type' => CoreType\EnumType::class,
                        'multiple' => true,
                        'class' => ProductStatus::class,
                    ],
                    'validityStartAt' => [
                        'expected_type' => CoreType\DateTimeType::class,
                        'input' => 'datetime_immutable',
                    ],
                    'validityEndAt' => [
                        'expected_type' => CoreType\DateTimeType::class,
                        'input' => 'datetime_immutable',
                    ],
                    'desc' => [
                        'expected_type' => CoreType\TextareaType::class,
                        'attr' => [
                            'rows' => 2,
                        ],
                        'property_path' => 'description',
                    ],
                ],
            ),
        ];

        yield 'Dto - Product1 with children_excluded = *' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_excluded' => '*',
                ],
                expectedForm: [],
            ),
        ];

        yield 'Dto - Product1 with children_excluded = *, custom overrides' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_excluded' => '*',
                    'children' => [
                        'title' => [],
                        'code' => [
                            'label' => 'product.code_label',
                            'required' => false,
                        ],
                        'description' => [
                            'attr' => [
                                'rows' => 4,
                            ],
                        ],
                    ],
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => CoreType\IntegerType::class,
                        'label' => 'product.code_label',
                        'required' => false,
                    ],
                    'desc' => [
                        'expected_type' => CoreType\TextareaType::class,
                        'attr' => [
                            'rows' => 4,
                        ],
                        'property_path' => 'description',
                    ],
                ],
            ),
        ];

        yield 'Dto - Product1 with children_excluded & child_excluded' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_excluded' => ['tags', 'mediaMain', 'mediaColl', 'status', 'statusList', 'validityStartAt', 'validityEndAt'],
                    'children' => [
                        'code' => [
                            'child_excluded' => true,
                        ],
                        'description' => [
                            'child_excluded' => true,
                        ],
                    ],
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                ],
            ),
        ];

        yield 'Dto - Product1 with children_groups' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_groups' => ['grp1'],
                ],
                expectedForm: [
                ],
            ),
        ];

        yield 'Dto - Product1 with children_groups & child_groups' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_groups' => ['grp1', 'grp2'],
                    'children' => [
                        'title' => [
                            'child_groups' => ['grp1'],
                        ],
                        'code' => [
                            'child_groups' => ['grp2'],
                        ],
                        'description' => [
                            'child_groups' => ['grp1', 'grp2'],
                        ],
                    ],
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => CoreType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => CoreType\IntegerType::class,
                    ],
                    'desc' => [
                        'expected_type' => CoreType\TextareaType::class,
                    ],
                ],
            ),
        ];

        yield 'Dto - Product1 with children & builder callables' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_excluded' => '*',
                    'children' => [
                        'description' => static fn (FormBuilderInterface $builder, array $propAttributeOptions): FormBuilderInterface => $builder->create('description', CoreType\TextareaType::class, [
                            'attr' => $propAttributeOptions['attr'],
                            'label' => 'product.description_label',
                        ]),
                        '_ignoredNaming_' => static fn (FormBuilderInterface $builder): FormBuilderInterface => $builder
                            ->create('validity_range', CoreType\FormType::class, ['inherit_data' => true])
                            ->add('validityStartAt', CoreType\DateType::class)
                            ->add('validityEndAt', CoreType\DateType::class),
                        'agreement' => [
                            'child_type' => CoreType\CheckboxType::class,
                            'mapped' => false,
                        ],
                    ],
                    'builder' => static function (FormBuilderInterface $builder, array $classProperties): void {
                        $builder->add('save', CoreType\SubmitType::class);
                    },
                ],
                expectedForm: [
                    'description' => [
                        'expected_type' => CoreType\TextareaType::class,
                        'label' => 'product.description_label',
                        'attr' => [
                            'rows' => 2,
                        ],
                    ],
                    'validity_range' => [
                        'expected_type' => CoreType\FormType::class,
                        'expected_children' => [
                            'validityStartAt' => [
                                'expected_type' => CoreType\DateType::class,
                            ],
                            'validityEndAt' => [
                                'expected_type' => CoreType\DateType::class,
                            ],
                        ],
                    ],
                    'agreement' => [
                        'expected_type' => CoreType\CheckboxType::class,
                        'mapped' => false,
                    ],
                    'save' => [
                        'expected_type' => CoreType\SubmitType::class,
                    ],
                ],
            ),
        ];
    }
}
