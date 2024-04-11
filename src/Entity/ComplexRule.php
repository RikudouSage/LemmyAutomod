<?php

namespace App\Entity;

use App\Automod\Enum\ComplexRuleType;
use App\Enum\RunConfiguration;
use App\Repository\ComplexRuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'complex_rules')]
#[ORM\Entity(repositoryClass: ComplexRuleRepository::class)]
class ComplexRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, enumType: ComplexRuleType::class)]
    private ?ComplexRuleType $type = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $rule = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $actions = null;

    #[ORM\Column(length: 180, enumType: RunConfiguration::class, options: ['default' => RunConfiguration::WhenNotAborted->value])]
    private RunConfiguration $runConfiguration = RunConfiguration::WhenNotAborted;

    #[ORM\Column(options: ['default' => true])]
    private bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?ComplexRuleType
    {
        return $this->type;
    }

    public function setType(ComplexRuleType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(string $rule): static
    {
        $this->rule = $rule;

        return $this;
    }

    public function getActions(): ?string
    {
        return $this->actions;
    }

    public function setActions(string $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function getRunConfiguration(): RunConfiguration
    {
        return $this->runConfiguration;
    }

    public function setRunConfiguration(RunConfiguration $runConfiguration): static
    {
        $this->runConfiguration = $runConfiguration;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}
