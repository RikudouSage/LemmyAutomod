<?php

namespace App\Entity;

use App\Repository\IgnoredPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'ignored_posts')]
#[ORM\Entity(repositoryClass: IgnoredPostRepository::class)]
class IgnoredPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?int $postId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): static
    {
        $this->postId = $postId;

        return $this;
    }
}
