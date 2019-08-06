<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model;

use DateTimeInterface;

interface AuthorizationCodeInterface
{
    /**
     * Revoke access token
     */
    public function revoke(): void;

    /**
     * Check if access token is revoked
     *
     * @return bool
     */
    public function isRevoked(): bool;

    /**
     * Return access token identifier
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Return identity identifier
     *
     * @return string|null
     */
    public function getIdentityId(): ?string;

    /**
     * Return expiration time of access token
     *
     * @return DateTimeInterface
     */
    public function getExpiry(): DateTimeInterface;

    /**
     * Return entity scopes of access token
     *
     * @return ScopeInterface[]
     */
    public function getScopes(): array;
}