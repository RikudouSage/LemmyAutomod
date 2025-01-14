<?php

namespace App\Entity;

use App\Helper\DisableableEntity;
use App\Repository\WatchedUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Table(name: 'watched_users')]
#[ORM\Entity(repositoryClass: WatchedUserRepository::class)]
class WatchedUser implements ResolvableUserEntity
{
    use DisableableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $username = null;

    #[ApiProperty]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $instance = null;

    #[ApiProperty]
    #[ORM\Column(nullable: true)]
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
