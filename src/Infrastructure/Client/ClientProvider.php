<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Client;

use Illuminate\Database\Eloquent\Collection;
use MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode\AuthCodeModel;

class ClientProvider
{
    /**
     * @var ClientModel
     */
    private $model;

    public function __construct(ClientModel $clientModel)
    {
        $this->model = $clientModel;
    }

    public function clientOfIdentifier(string $identifier): ?ClientModel
    {
        return $this->model
            ->newModelQuery()
            ->where('identifier', $identifier)
            ->first();
    }

    public function usersOfClient(string $identifier): Collection
    {
        // fix oauth identity model
    }

    public function revokeAllClientAuthCodes(string $identifier): void
    {
        /** @var ClientModel $client */
        if ($client = $this->model->newModelQuery()->where('identifier', $identifier)->first()) {
            $client
                ->authCodes()
                ->where('revoked', 0)
                ->where('client_id', $identifier)
                ->each(function (AuthCodeModel $model) {
                    $model->revoke();
                });
        }
    }

    public function store(array $data): ClientModel
    {
        return $this->model->newModelQuery()->create($data);
    }
}