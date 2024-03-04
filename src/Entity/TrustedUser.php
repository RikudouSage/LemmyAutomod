<?php

namespace App\Entity;

use App\Helper\DisableableEntity;
use App\Repository\TrustedUserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\UniqueConstraint(fields: ['username', 'instance'])]
#[ORM\Table(name: 'trusted_users')]
#[ORM\Entity(repositoryClass: TrustedUserRepository::class)]
class TrustedUser
{
    use DisableableEntity;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $username = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $instance = null;

    #[ORM\Column(unique: true, nullable: true)]
    private ?int $userId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getInstance(): ?string
    {
        return $this->instance;
    }

    public function setInstance(?string $instance): static
    {
        $this->instance = $instance;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;

        return $this;
    }
}
