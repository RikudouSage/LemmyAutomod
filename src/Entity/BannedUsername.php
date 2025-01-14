<?php

namespace App\Entity;

use App\Helper\DisableableEntity;
use App\Repository\BannedUsernameRepository;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Entity(repositoryClass: BannedUsernameRepository::class)]
#[ORM\Table(name: 'banned_usernames')]
class BannedUsername
{
    use DisableableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(length: 180)]
    private ?string $username = null;

    #[ApiProperty]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $reason = null;

    #[ApiProperty(getter: 'shouldRemoveAll')]
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
