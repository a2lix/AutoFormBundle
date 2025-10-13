<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Tests\Form\Type;

use A2lix\AutoFormBundle\Form\Type\AutoType;
use A2lix\AutoFormBundle\Tests\Fixtures\Entity\Product1;
use A2lix\AutoFormBundle\Tests\Form\TypeTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\Extension\Core\Type as FormType;

/**
 * @internal
 */
#[CoversNothing]
final class AutoTypeEntityTest extends TypeTestCase
{
    #[DataProvider('provideScenarioCases')]
    public function testScenario(TestScenario $testScenario): void
    {
        $form = $this->factory
            ->createBuilder(AutoType::class, $testScenario->obj, $testScenario->formOptions)
            ->getForm()
        ;

        self::assertFormChildren($testScenario->expectedForm, $form->all());
    }

    /**
     * @return \Iterator<array<int, TestScenario>>
     */
    public static function provideScenarioCases(): iterable
    {
        yield 'Product1 with default behavior, no options' => [
            new TestScenario(
                obj: new Product1(),
                expectedForm: [
                    'title' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => FormType\IntegerType::class,
                    ],
                    'tags' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'mediaMain' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'mediaColl' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'status' => [
                        'expected_type' => FormType\EnumType::class,
                    ],
                    'validityStartAt' => [
                        'expected_type' => FormType\DateTimeType::class,
                    ],
                    'validityEndAt' => [
                        'expected_type' => FormType\DateTimeType::class,
                    ],
                    'description' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                ],
            ),
        ];

        yield 'Product1 with children_embedded = *' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_embedded' => '*',
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => FormType\IntegerType::class,
                    ],
                    'tags' => [
                        'expected_type' => FormType\CollectionType::class,
                        'entry_type' => 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType',
                        'entry_options' => [
                            'block_name' => 'entry',
                        ],
                    ],
                    'mediaMain' => [
                        'expected_type' => AutoType::class,
                        'expected_children' => [
                            'url' => [
                                'expected_type' => FormType\TextType::class,
                                'help' => 'media.url_help',
                            ],
                            'description' => [
                                'expected_type' => FormType\TextareaType::class,
                            ],
                        ],
                    ],
                    'mediaColl' => [
                        'expected_type' => FormType\CollectionType::class,
                        'entry_type' => 'A2lix\\AutoFormBundle\\Form\\Type\\AutoType',
                        'entry_options' => [
                            'data_class' => 'A2lix\\AutoFormBundle\\Tests\\Fixtures\\Entity\\Media1',
                            'block_name' => 'entry',
                        ],
                    ],
                    'status' => [
                        'expected_type' => FormType\EnumType::class,
                    ],
                    'validityStartAt' => [
                        'expected_type' => FormType\DateTimeType::class,
                    ],
                    'validityEndAt' => [
                        'expected_type' => FormType\DateTimeType::class,
                    ],
                    'description' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                ],
            ),
        ];

        yield 'Product1 with children_embedded = [mediaColl]' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_embedded' => ['mediaColl'],
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => FormType\IntegerType::class,
                    ],
                    'tags' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'mediaMain' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'mediaColl' => [
                        'expected_type' => FormType\CollectionType::class,
                        'entry_options' => [
                            'data_class' => 'A2lix\\AutoFormBundle\\Tests\\Fixtures\\Entity\\Media1',
                            'block_name' => 'entry',
                        ],
                    ],
                    'status' => [
                        'expected_type' => FormType\EnumType::class,
                    ],
                    'validityStartAt' => [
                        'expected_type' => FormType\DateTimeType::class,
                    ],
                    'validityEndAt' => [
                        'expected_type' => FormType\DateTimeType::class,
                    ],
                    'description' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                ],
            ),
        ];

        yield 'Product1 with children_excluded = *' => [
            new TestScenario(
                obj: new Product1(),
                formOptions: [
                    'children_excluded' => '*',
                ],
                expectedForm: [],
            ),
        ];

        yield 'Product1 with children_excluded = *, custom selection with overrides' => [
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
                            'child_type' => FormType\TextareaType::class,
                        ],
                    ],
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => FormType\IntegerType::class,
                        'label' => 'product.code_label',
                        'required' => false,
                    ],
                    'description' => [
                        'expected_type' => FormType\TextareaType::class,
                    ],
                ],
            ),
        ];
    }
}
