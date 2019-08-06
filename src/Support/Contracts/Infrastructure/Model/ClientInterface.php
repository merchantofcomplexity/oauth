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
     * Return client identifier
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Return client secret
     *
     * @return string
     */
    public function getSecret(): string;

    /**
     * Return client application name
     *
     * @return string
     */
    public function getAppName(): string;

    /**
     * Return redirect uris
     *
     * @return string[]
     */
    public function getRedirectUris(): array;

    /**
     * Return grants type of client
     *
     * @return string[]
     */
    public function getGrants(): array;
}