<?php

namespace App\Entity;

use App\Helper\DisableableEntity;
use App\Repository\BannedImageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: BannedImageRepository::class)]
#[ORM\Table(name: 'banned_images')]
class BannedImage
{
    use DisableableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(length: 180)]
    private ?string $imageHash = null;

    #[ApiProperty]
    #[ORM\Column(options: ['default' => 100])]
    private float $similarityPercent = 100.0;

    #[ApiProperty(getter: 'shouldRemoveAll')]
    #[ORM\Column(options: ['default' => false])]
    private bool $removeAll = false;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageHash(): ?string
    {
        return $this->imageHash;
    }

    public function setImageHash(string $imageHash): static
    {
        $this->imageHash = $imageHash;

        return $this;
    }

    public function getSimilarityPercent(): float
    {
        return $this->similarityPercent;
    }

    public function setSimilarityPercent(float $similarityPercent): static
    {
        $this->similarityPercent = $similarityPercent;

        return $this;
    }

    public function shouldRemoveAll(): bool
    {
        return $this->removeAll;
    }

    public function setRemoveAll(bool $removeAll): static
    {
        $this->removeAll = $removeAll;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
