<?php

namespace App\Entity;

use App\Repository\IgnoredCommentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'ignored_comments')]
#[ORM\Entity(repositoryClass: IgnoredCommentRepository::class)]
class IgnoredComment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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
