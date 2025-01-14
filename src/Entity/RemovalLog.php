<?php

namespace App\Entity;

use App\Repository\RemovalLogRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: RemovalLogRepository::class)]
#[ORM\Table(name: 'removal_logs')]
#[ORM\Index(fields: ['type', 'targetId'])]
class RemovalLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(length: 180)]
    private ?string $type = null;

    #[ApiProperty]
    #[ORM\Column]
    private ?int $targetId = null;

    #[ApiProperty]
    #[ORM\Column]
    private ?DateTimeImmutable $validUntil = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTargetId(): ?int
    {
        return $this->targetId;
    }

    public function setTargetId(int $targetId): static
    {
        $this->targetId = $targetId;

        return $this;
    }

    public function getValidUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }

    public function setValidUntil(DateTimeImmutable $validUntil): static
    {
        $this->validUntil = $validUntil;

        return $this;
    }
}
