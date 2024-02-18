<?php

namespace App\Entity;

use App\Repository\BannedUsernameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BannedUsernameRepository::class)]
#[ORM\Table(name: 'banned_usernames')]
class BannedUsername
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $removeAll = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

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

    public function shouldRemoveAll(): bool
    {
        return $this->removeAll;
    }

    public function setRemoveAll(bool $removeAll): static
    {
        $this->removeAll = $removeAll;

        return $this;
    }
}
