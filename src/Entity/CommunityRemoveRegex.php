<?php

namespace App\Entity;

use App\Helper\DisableableEntity;
use App\Repository\CommunityRemoveRegexRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Table(name: 'community_remove_regexes')]
#[ORM\Entity(repositoryClass: CommunityRemoveRegexRepository::class)]
class CommunityRemoveRegex
{
    use DisableableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(length: 180)]
    private ?string $regex = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ApiProperty(getter: 'shouldBanModerators')]
    #[ORM\Column(options: ['default' => false])]
    private bool $banModerators = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRegex(): ?string
    {
        return $this->regex;
    }

    public function setRegex(string $regex): static
    {
        $this->regex = $regex;

        return $this;
    }

    public function shouldBanModerators(): bool
    {
        return $this->banModerators;
    }

    public function setBanModerators(bool $banModerators): static
    {
        $this->banModerators = $banModerators;

        return $this;
    }
}
