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
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Form\Extension\Core\Type as FormType;

/**
 * @internal
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @coversNothing
 */
final class AutoTypeDtoTest extends TypeTestCase
{
    #[DataProvider('provideScenarios')]
    public function testScenario(DtoScenario $scenario): void
    {
        $form = $this->factory
            ->createBuilder(AutoType::class, $scenario->dto, $scenario->formOptions)
            ->getForm()
        ;

        self::assertSame(array_keys($scenario->expectedForm), array_keys($form->all()));
        foreach ($form->all() as $childName => $child) {
            /** @var string $childName */
            /** @psalm-suppress PossiblyUndefinedArrayOffset */
            if (null !== $expectedType = $scenario->expectedForm[$childName]['expected_type'] ?? null) {
                self::assertSame($expectedType, $child->getConfig()->getType()->getInnerType()::class);
            }

            $expectedPartialOptions = $scenario->expectedForm[$childName];
            $actualOptions = $child->getConfig()->getOptions();

            self::assertSame($expectedPartialOptions, array_intersect_key($actualOptions, $expectedPartialOptions), $childName);
        }
    }

    public static function provideScenarios(): iterable
    {
        yield 'Product1 without formOptions' => [
            new DtoScenario(
                dto: new Product1(),
                expectedForm: [
                    'title' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'code' => [
                        'expected_type' => FormType\TextType::class,
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
                        'expected_type' => FormType\TextType::class,
                    ],
                    'validityStartAt' => [
                        'expected_type' => FormType\TextType::class,
                    ],
                    'validityEndAt' => [
                        'expected_type' => FormType\TextType::class,
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
