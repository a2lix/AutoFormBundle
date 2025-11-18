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
use A2lix\AutoFormBundle\Tests\Fixtures\ProductStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

class Product1
{
    /**
     * @param list<string>            $tags
     * @param Collection<int, Media1> $mediaColl
     * @param list<ProductStatus>     $statusList
     */
    public function __construct(
        #[AutoTypeCustom(excluded: true)]
        public readonly ?string $id = null,
        public readonly ?string $title = null,
        #[AutoTypeCustom(type: CoreType\TextareaType::class, name: 'desc', options: ['attr' => ['rows' => 2]])]
        private ?string $description = null,
        public readonly ?int $code = null,
        public readonly array $tags = [],
        public readonly ?Media1 $mediaMain = null,
        public ?Collection $mediaColl = null,
        public readonly ?ProductStatus $status = null,
        public readonly ?array $statusList = null,
        public readonly ?\DateTimeImmutable $validityStartAt = null,
        public readonly ?\DateTimeImmutable $validityEndAt = null,
        // @phpstan-ignore property.onlyWritten
        private ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->mediaColl ??= new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

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
