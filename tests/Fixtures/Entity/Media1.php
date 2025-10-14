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

use A2lix\AutoFormBundle\Form\Attribute\AutoTypeCustom;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

#[ORM\Entity]
class Media1
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue]
    public ?int $id = null;

    #[ORM\Column]
    #[AutoTypeCustom(excluded: true)]
    public \DateTimeImmutable $createdAt;

    #[ORM\Column]
    #[AutoTypeCustom(options: ['help' => 'media.url_help'])]
    public string $url;

    #[ORM\Column(nullable: true)]
    #[AutoTypeCustom(type: CoreType\TextareaType::class)]
    public ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Product1::class, inversedBy: 'mediaColl')]
    #[ORM\JoinColumn(name: 'product_id', referencedColumnName: 'id', nullable: false)]
    #[AutoTypeCustom(excluded: true)]
    public Product1 $product;
}
