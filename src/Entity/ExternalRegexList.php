<?php

namespace App\Entity;

use App\Repository\ExternalRegexListRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;
use Rikudou\JsonApiBundle\Attribute\ApiResource;

#[ApiResource]
#[ORM\Table(name: 'external_regex_lists')]
#[ORM\Entity(repositoryClass: ExternalRegexListRepository::class)]
class ExternalRegexList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $url = null;

    #[ApiProperty]
    #[ORM\Column(length: 255)]
    private string $delimiter = "\n";

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $prepend = null;

    #[ApiProperty]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $append = null;

    #[ApiProperty]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function setDelimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function getPrepend(): ?string
    {
        return $this->prepend;
    }

    public function setPrepend(?string $prepend): static
    {
        $this->prepend = $prepend;

        return $this;
    }

    public function getAppend(): ?string
    {
        return $this->append;
    }

    public function setAppend(?string $append): static
    {
        $this->append = $append;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
