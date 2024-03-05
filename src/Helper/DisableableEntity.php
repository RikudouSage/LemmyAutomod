<?php

namespace App\Helper;

use Doctrine\ORM\Mapping as ORM;

trait DisableableEntity
{
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
