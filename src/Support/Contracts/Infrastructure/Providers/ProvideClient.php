<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers;

use Illuminate\Database\Eloquent\Collection;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;

interface ProvideClient
{
    /**
     * Find client by his identifier
     *
     * @param string $identifier
     * @return ClientModel|null
     */
    public function clientOfIdentifier(string $identifier): ?ClientModel;

    /**
     * Find all users of client
     *
     * @param string $identifier
     * @return Collection
     */
    public function usersOfClient(string $identifier): Collection;

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
     * @return ClientModel
     */
    public function store(array $data): ClientModel;
}