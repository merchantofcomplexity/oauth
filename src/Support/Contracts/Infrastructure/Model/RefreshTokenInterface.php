<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model;

use DateTimeInterface;

interface RefreshTokenInterface
{
    /**
     * Revoke Refresh token
     */
    public function revoke(): void;

    /**
     * Check if refresh token is revoked
     *
     * @return bool
     */
    public function isRevoked(): bool;

    /**
     * Return refresh token identifier
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Return expiration time of access token
     *
     * @return DateTimeInterface
     */
    public function getExpiry(): DateTimeInterface;
}