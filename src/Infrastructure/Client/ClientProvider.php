<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Client;

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
}