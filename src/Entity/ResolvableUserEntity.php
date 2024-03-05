<?php

namespace App\Entity;

interface ResolvableUserEntity
{
    public function getUsername(): ?string;
    public function getInstance(): ?string;
    public function getUserId(): ?int;
    public function setUserId(?int $userId): static;
}
