<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model;

use DateTimeInterface;

interface AccessTokenInterface
{
    public function revoke(): void;

    public function isRevoked(): bool;

    public function getId(): string;

    public function getIdentityId(): ?string;

    public function getExpiry(): DateTimeInterface;

    /**
     * @return ScopeInterface[]
     */
    public function getScopes(): array;
}