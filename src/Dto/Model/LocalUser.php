<?php

namespace App\Dto\Model;

final readonly class LocalUser
{
    public function __construct(
        public int     $id,
        public int     $personId,
        public ?string $email,
        public bool    $emailVerified,
        public bool    $acceptedApplication,
        public bool    $admin,
        public bool    $totp2faEnabled,
    ) {
    }
}
