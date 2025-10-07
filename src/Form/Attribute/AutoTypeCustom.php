<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Form\Attribute;

use A2lix\AutoFormBundle\Form\Type\AutoType;

/**
 * @psalm-import-type childOptions from AutoType
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class AutoTypeCustom
{
    /**
     * @param array<string, mixed> $options
     * @param class-string|null    $type
     */
    public function __construct(
        private array $options = [],
        private ?string $type = null,
        private ?string $name = null,
        private ?bool $excluded = null,
        private ?bool $embedded = null,
    ) {}

    /**
     * @return childOptions
     */
    public function getOptions(): array
    {
        return [
            ...$this->options,
            ...(null !== $this->type ? ['child_type' => $this->type] : []),
            ...(null !== $this->name ? ['child_name' => $this->name] : []),
            ...(null !== $this->excluded ? ['child_excluded' => $this->excluded] : []),
            ...(null !== $this->embedded ? ['child_embedded' => $this->embedded] : []),
        ];
    }
}
