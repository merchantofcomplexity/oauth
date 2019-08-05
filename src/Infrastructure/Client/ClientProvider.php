<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode\AuthCodeModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;

class ClientProvider implements ProvideClient
{
    /**
     * @var ClientInterface|Model
     */
    private $model;

    public function __construct(ClientInterface $clientModel)
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
        // fixMe
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