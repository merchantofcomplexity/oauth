<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model;

interface ClientInterface
{
    /**
     * Revoke client
     */
    public function revoke(): void;

    /**
     * Check if client is revoked
     *
     * @return bool
     */
    public function isRevoked(): bool;

    /**
     * Client identifier
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Client secret
     *
     * @return string
     */
    public function getSecret(): string;

    /**
     * Client application name
     *
     * @return string
     */
    public function getAppName(): string;

    /**
     * Redirect uris
     *
     * @return string[]
     */
    public function getRedirectUris(): array;

    /**
     * Grants type
     *
     * @return string[]
     */
    public function getGrants(): array;
}