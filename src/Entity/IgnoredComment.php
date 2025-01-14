<?php

namespace App\Entity;

use App\Repository\IgnoredCommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Table(name: 'ignored_comments')]
#[ORM\Entity(repositoryClass: IgnoredCommentRepository::class)]
class IgnoredComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(unique: true)]
    private ?int $commentId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommentId(): ?int
    {
        return $this->commentId;
    }

    public function setCommentId(int $commentId): static
    {
        $this->commentId = $commentId;

        return $this;
    }
}
