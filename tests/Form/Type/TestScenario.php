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

/**
 * @psalm-type ExpectedChildren = array<string, array{
 *   expected_type?: class-string,
 *   expected_children?: mixed,
 *   ...
 * }>
 */
class TestScenario
{
    /**
     * @param ExpectedChildren $expectedForm
     */
    public function __construct(
        public readonly ?object $obj,
        public readonly array $formOptions = [],
        public readonly array $expectedForm = [],
    ) {}
}
