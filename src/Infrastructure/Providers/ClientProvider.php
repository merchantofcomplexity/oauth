<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MerchantOfComplexity\Oauth\Infrastructure\Models\AuthCodeModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AccessTokenInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;

class ClientProvider implements ProvideClient
{
    /**
     * @var WithClient|Model
     */
    private $model;

    public function __construct(WithClient $clientModel)
    {
        $this->model = $clientModel;
    }

    public function clientOfIdentifier(string $identifier): ?ClientInterface
    {
        return $this->model
            ->newModelQuery()
            ->where('identifier', $identifier)
            ->first();
    }

    public function applicationsOfIdentity(string $identityId): Collection
    {
        return $this->model
            ->newModelQuery()
            ->where('identity_id', $identityId)
            ->get();
    }

    public function usersOfClient(string $identifier): Collection
    {
        // checkMe optimize query

        $client = $this->model
            ->newModelQuery()
            ->with('tokens.identity')
            ->where('revoked', 0)
            ->where('identifier', $identifier)
            ->first();

        if ($client) {
            return $client->getRelation('tokens')->groupBy(function (AccessTokenInterface $accessToken) {
                return $accessToken->getIdentityId();
            });
        }

        return new Collection();
    }

    public function revokeAuthCodesByClientId(string $identifier): void
    {
        /** @var WithClient $client */
        if ($client = $this->clientOfIdentifier($identifier)) {
            $client
                ->authCodes()
                ->where('revoked', 0)
                ->where('client_id', $identifier)
                ->each(function (AuthCodeModel $model) {
                    $model->revoke();
                });
        }
    }

    public function store(array $data): void
    {
        $this->model->newModelQuery()->create($data);
    }
}