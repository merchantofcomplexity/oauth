<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Client;

use Illuminate\Database\Eloquent\Collection;

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

    public function store(array $data): ClientModel
    {
        return $this->model->newModelQuery()->create($data);
    }
}