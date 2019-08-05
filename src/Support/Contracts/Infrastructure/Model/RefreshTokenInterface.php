<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model;

use DateTimeInterface;

interface RefreshTokenInterface
{
    public function revoke(): void;

    public function isRevoked(): bool;

    public function getId(): string;

    public function getExpiry(): DateTimeInterface;
}