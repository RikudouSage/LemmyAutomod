<?php

namespace App\Entity;

use App\Repository\InstanceDefederationRuleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'instance_defederation_rules')]
#[ORM\Entity(repositoryClass: InstanceDefederationRuleRepository::class)]
class InstanceDefederationRule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $software = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allowOpenRegistrations = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allowOpenRegistrationsWithCaptcha = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allowOpenRegistrationsWithEmailVerification = null;

    #[ORM\Column(nullable: true)]
    private ?bool $allowOpenRegistrationsWithApplication = null;

    #[ORM\Column(nullable: true)]
    private ?bool $treatMissingDataAs = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $minimumVersion = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $evidence = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoftware(): ?string
    {
        return $this->software;
    }

    public function setSoftware(?string $software): static
    {
        $this->software = $software;

        return $this;
    }

    public function allowsOpenRegistrationsWithCaptcha(): ?bool
    {
        return $this->allowOpenRegistrationsWithCaptcha;
    }

    public function setAllowOpenRegistrationsWithCaptcha(bool $allowOpenRegistrationsWithCaptcha): static
    {
        $this->allowOpenRegistrationsWithCaptcha = $allowOpenRegistrationsWithCaptcha;

        return $this;
    }

    public function allowsOpenRegistrations(): ?bool
    {
        return $this->allowOpenRegistrations;
    }

    public function setAllowOpenRegistrations(bool $allowOpenRegistrations): static
    {
        $this->allowOpenRegistrations = $allowOpenRegistrations;

        return $this;
    }

    public function allowsOpenRegistrationsWithEmailVerification(): ?bool
    {
        return $this->allowOpenRegistrationsWithEmailVerification;
    }

    public function setAllowOpenRegistrationsWithEmailVerification(bool $allowOpenRegistrationsWithEmailVerification): static
    {
        $this->allowOpenRegistrationsWithEmailVerification = $allowOpenRegistrationsWithEmailVerification;

        return $this;
    }

    public function allowsOpenRegistrationsWithApplication(): ?bool
    {
        return $this->allowOpenRegistrationsWithApplication;
    }

    public function setAllowOpenRegistrationsWithApplication(?bool $allowOpenRegistrationsWithApplication): static
    {
        $this->allowOpenRegistrationsWithApplication = $allowOpenRegistrationsWithApplication;

        return $this;
    }

    public function getTreatMissingDataAs(): ?bool
    {
        return $this->treatMissingDataAs;
    }

    public function setTreatMissingDataAs(?bool $treatMissingDataAs): static
    {
        $this->treatMissingDataAs = $treatMissingDataAs;

        return $this;
    }

    public function getMinimumVersion(): ?string
    {
        return $this->minimumVersion;
    }

    public function setMinimumVersion(?string $minimumVersion): static
    {
        $this->minimumVersion = $minimumVersion;

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

    public function getEvidence(): ?string
    {
        return $this->evidence;
    }

    public function setEvidence(?string $evidence): static
    {
        $this->evidence = $evidence;

        return $this;
    }
}
