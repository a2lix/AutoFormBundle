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
use A2lix\AutoFormBundle\Tests\Fixtures\Dto\Product1;
use A2lix\AutoFormBundle\Tests\Form\TypeTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\Extension\Core\Type as FormType;

/**
 * @internal
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
#[CoversNothing]
final class AutoTypeDtoTest extends TypeTestCase
{
    #[DataProvider('provideScenarioCases')]
    public function testScenario(DtoScenario $dtoScenario): void
    {
        $form = $this->factory
            ->createBuilder(AutoType::class, $dtoScenario->dto, $dtoScenario->formOptions)
            ->getForm()
        ;

        self::assertSame(array_keys($dtoScenario->expectedForm), array_keys($form->all()));
        foreach ($form->all() as $childName => $child) {
            /** @var string $childName */
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            if (null !== $expectedType = $dtoScenario->expectedForm[$childName]['expected_type'] ?? null) {
                self::assertSame($child->getConfig()->getType()->getInnerType()::class, $expectedType, \sprintf('Type of "%s"', $childName));
            }

            $expectedPartialOptions = $dtoScenario->expectedForm[$childName];
            unset($expectedPartialOptions['expected_type']);
            $actualOptions = $child->getConfig()->getOptions();

            self::assertSame($expectedPartialOptions, array_intersect_key($actualOptions, $expectedPartialOptions), \sprintf('Options of "%s"', $childName));
        }
    }

    /**
     * @return \Iterator<array<int, DtoScenario>>
     */
    public static function provideScenarioCases(): iterable
    {
        yield 'Product1 with default behavior, no options' => [
            new DtoScenario(
                dto: new Product1(),
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
            new DtoScenario(
                dto: new Product1(),
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
                    ],
                    'mediaMain' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'mediaColl' => [
                        'expected_type' => FormType\CollectionType::class,
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
            new DtoScenario(
                dto: new Product1(),
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
            new DtoScenario(
                dto: new Product1(),
                formOptions: [
                    'children_excluded' => '*',
                ],
                expectedForm: [],
            ),
        ];

        yield 'Product1 with children_excluded = *, custom selection' => [
            new DtoScenario(
                dto: new Product1(),
                formOptions: [
                    'children_excluded' => '*',
                    'children' => [
                        'title' => [],
                        'code' => [],
                        'description' => [],
                    ],
                ],
                expectedForm: [
                    'title' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => FormType\IntegerType::class,
                    ],
                    'description' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                ],
            ),
        ];
    }
}

class DtoScenario
{
    /**
     * @param array<string, array{expected_type?: class-string, ...}> $expectedForm
     */
    public function __construct(
        public readonly ?object $dto,
        public readonly array $formOptions = [],
        public readonly array $expectedForm = [],
    ) {}
}
