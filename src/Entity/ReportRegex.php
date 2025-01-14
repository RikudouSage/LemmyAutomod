<?php

namespace App\Entity;

use App\Helper\DisableableEntity;
use App\Repository\ReportRegexRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource(plural: 'report-regexes')]
#[ORM\Entity(repositoryClass: ReportRegexRepository::class)]
#[ORM\Table(name: 'report_regexes')]
class ReportRegex
{
    use DisableableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $regex = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT)]
    private string $message = 'Reported by an automated rule, please check manually.';

    #[ApiProperty]
    #[ORM\Column(options: ['default' => false])]
    private bool $private = false;

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

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }
}
