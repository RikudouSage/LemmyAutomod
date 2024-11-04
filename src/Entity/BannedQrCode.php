<?php

namespace App\Entity;

use App\Repository\BannedQrCodeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BannedQrCodeRepository::class)]
#[ORM\Table(name: 'banned_qr_codes')]
class BannedQrCode
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $regex = null;

    #[ORM\Column]
    private ?bool $removeAll = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $reason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function setRegex(string $regex): static
    {
        $this->regex = $regex;

        return $this;
    }

    public function shouldRemoveAll(): ?bool
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
}
