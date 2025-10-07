<?php declare(strict_types=1);

/*
 * This file is part of the AutoFormBundle package.
 *
 * (c) David ALLIX <http://a2lix.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace A2lix\AutoFormBundle\Tests\Fixtures\Entity;

use A2lix\AutoFormBundle\Tests\Fixtures\ProductStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Product1
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column]
    public string $title;

    #[ORM\Column(nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    public int $code;

    #[ORM\Column]
    public array $tags = [];

    #[ORM\ManyToOne(targetEntity: Media1::class)]
    public ?Media1 $mediaMain = null;

    /**
     * @var Collection<int, Media1>
     */
    #[ORM\OneToMany(targetEntity: Media1::class, mappedBy: 'product', cascade: ['all'], orphanRemoval: true)]
    public Collection $mediaColl;

    #[ORM\Column]
    public ProductStatus $status;

    #[ORM\Column]
    public \DateTimeImmutable $validityStartAt;

    #[ORM\Column]
    public \DateTimeImmutable $validityEndAt;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->mediaColl = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }
}
