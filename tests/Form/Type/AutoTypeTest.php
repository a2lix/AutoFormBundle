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
use A2lix\AutoFormBundle\Tests\Form\DataProviderDto;
use A2lix\AutoFormBundle\Tests\Form\DataProviderEntity;
use A2lix\AutoFormBundle\Tests\Form\TestScenario;
use A2lix\AutoFormBundle\Tests\Form\TypeTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Symfony\Component\Form\FormInterface;

/**
 * @internal
 *
 * @phpstan-import-type ExpectedChildren from TestScenario
 */
#[CoversNothing]
final class AutoTypeTest extends TypeTestCase
{
    #[DataProviderExternal(DataProviderDto::class, 'provideScenarioCases')]
    #[DataProviderExternal(DataProviderEntity::class, 'provideScenarioCases')]
    public function testScenario(TestScenario $testScenario): void
    {
        $form = $this->factory
            ->createBuilder(AutoType::class, $testScenario->obj, $testScenario->formOptions)
            ->getForm()
        ;

        self::assertFormChildren($testScenario->expectedForm, $form->all());
    }

    /**
     * @param ExpectedChildren                       $expectedForm
     * @param array<array-key, FormInterface<mixed>> $formChildren
     */
    private static function assertFormChildren(array $expectedForm, array $formChildren, string $parentPath = ''): void
    {
        self::assertSame(array_keys($expectedForm), array_keys($formChildren));

        foreach ($formChildren as $childName => $child) {
            /** @var string $childName */
            $expectedChildOptions = $expectedForm[$childName];
            $childPath = $parentPath.'.'.$childName;

            if (null !== $expectedType = $expectedChildOptions['expected_type'] ?? null) {
                self::assertSame($expectedType, $child->getConfig()->getType()->getInnerType()::class, \sprintf('Type of "%s"', $childPath));
            }

            if (null !== $expectedChildren = $expectedChildOptions['expected_children'] ?? null) {
                // @phpstan-ignore argument.type
                self::assertFormChildren($expectedChildren, $child->all(), $childPath);
            }

            unset($expectedChildOptions['expected_type'], $expectedChildOptions['expected_children']);
            $actualOptions = $child->getConfig()->getOptions();

            // @phpstan-ignore nullCoalesce.variable, staticMethod.alreadyNarrowedType
            self::assertSame($expectedChildOptions, array_intersect_key($actualOptions, $expectedChildOptions ?? []), \sprintf('Options of "%s"', $childPath));
        }
    }
}
