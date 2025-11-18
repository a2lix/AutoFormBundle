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

namespace A2lix\AutoFormBundle\Form\Attribute;

use A2lix\AutoFormBundle\Form\Builder\AutoTypeBuilder;

/**
 * @phpstan-import-type ChildOptions from AutoTypeBuilder
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class AutoTypeCustom
{
    /**
     * @param array<string, mixed> $options
     * @param class-string|null    $type
     * @param list<string>|null    $groups
     */
    public function __construct(
        private array $options = [],
        private ?string $type = null,
        private ?string $name = null,
        private ?bool $excluded = null,
        private ?bool $embedded = null,
        private ?array $groups = null,
    ) {}

    /**
     * @return ChildOptions
     */
    public function getOptions(): array
    {
        /** @var ChildOptions */
        return [
            ...$this->options,
            ...(null !== $this->type ? ['child_type' => $this->type] : []),
            ...(null !== $this->name ? ['child_name' => $this->name] : []),
            ...(null !== $this->excluded ? ['child_excluded' => $this->excluded] : []),
            ...(null !== $this->embedded ? ['child_embedded' => $this->embedded] : []),
            ...(null !== $this->groups ? ['child_groups' => $this->groups] : []),
        ];
    }
}
