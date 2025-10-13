<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Tests\Fixtures\Dto;

use A2lix\AutoFormBundle\Form\Attribute\AutoTypeCustom;
use Symfony\Component\Form\Extension\Core\Type as FormType;

class Media1
{
    public function __construct(
        public readonly ?string $id = null,

        #[AutoTypeCustom(options: ['help' => 'media.url_help'])]
        public readonly ?string $url = null,

        #[AutoTypeCustom(type: FormType\TextareaType::class)]
        private ?string $description = null,
    ) {}

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
