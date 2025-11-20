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

/**
 * @phpstan-type ExpectedChildren = array<string, array{
 *   expected_type?: class-string,
 *   expected_children?: mixed,
 *   ...
 * }>
 */
final readonly class TestScenario
{
    /**
     * @param array<string, mixed> $formOptions
     * @param ExpectedChildren     $expectedForm
     */
    public function __construct(
        public ?object $obj,
        public array $formOptions = [],
        public array $expectedForm = [],
    ) {}
}
