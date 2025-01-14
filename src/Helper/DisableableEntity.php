<?php

namespace App\Helper;

use Doctrine\ORM\Mapping as ORM;
use Rikudou\JsonApiBundle\Attribute\ApiProperty;

trait DisableableEntity
{
    #[ApiProperty]
    #[ORM\Column(options: ['default' => true])]
    private bool $enabled = true;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
