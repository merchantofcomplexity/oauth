<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientProvider;
use MerchantOfComplexity\Oauth\League\Entity\Client;

final class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var ClientProvider
     */
    private $clientProvider;

    public function __construct(ClientProvider $clientProvider)
    {
        $this->clientProvider = $clientProvider;
    }

    public function getClientEntity($clientIdentifier): ?ClientEntityInterface
    {
        $clientModel = $this->clientProvider->clientOfIdentifier($clientIdentifier);

        if (!$clientModel || !$clientModel->isActive()) {
            return null;
        }

        $client = new Client();

        $client->setIdentifier($client->getIdentifier());
        $client->setRedirectUri(array_map('strval', $client->getRedirectUri()));

        return $client;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $clientModel = $this->clientProvider->clientOfIdentifier($clientIdentifier);

        if (!$this->isGrantSupported($clientModel, $grantType)) {
            return false;
        }

        return $this->checkCredentials($clientModel, $clientSecret);
    }

    protected function isGrantSupported(ClientModel $client, ?string $grant): bool
    {
        if (null === $grant || empty($grants = $client->getGrants())) {
            return true;
        }

        return in_array($grant, $client->getGrants());
    }

    protected function checkCredentials(ClientModel $clientModel, ?string $secret): bool
    {
        if (!$secret) {
            return true;
        }

        return hash_equals($clientModel->getSecret(), $secret);
    }
}