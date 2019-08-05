<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use Illuminate\Support\Collection;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;

interface ProvideClient
{
    /**
     * Find client by his identifier
     *
     * @param string $identifier
     * @return ClientInterface|null
     */
    public function clientOfIdentifier(string $identifier): ?ClientInterface;

    /**
     * Find all users of client
     *
     * @param string $identifier
     * @return Collection
     */
    public function usersOfClient(string $identifier): Collection;

    /**
     * Find all applications of identity
     *
     * @param string $identityId
     * @return Collection
     */
    public function applicationsOfIdentity(string $identityId): Collection;

    /**
     * Revoke all authorization codes by client identifier
     *
     * @param string $identifier
     */
    public function revokeAuthCodesByClientId(string $identifier): void;

    /**
     * Persist new client
     *
     * @param array $data
     */
    public function store(array $data): void;
}