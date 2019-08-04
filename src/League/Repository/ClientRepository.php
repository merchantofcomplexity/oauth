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

        if (!$clientModel || $clientModel->isRevoked()) {
            return null;
        }

        $client = new Client();

        $client->setIdentifier($clientModel->getId());
        $client->setRedirectUri(array_map('strval', $clientModel->getRedirectUris()));

        return $client;
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $clientModel = $this->clientProvider->clientOfIdentifier($clientIdentifier);

        if (!$clientModel) {
            return false;
        }

        if (!$this->isGrantSupported($clientModel, $grantType)) {
            return false;
        }

        return $this->checkCredentials($clientModel, $clientSecret);
    }

    // todo extend interface
    public function createClient(string $identityId, string $appName, array $scopes, array $redirectUris): ClientModel
    {
        $identifier = hash('md5', random_bytes(16));
        $secret = hash('sha512', random_bytes(32));

        $data = [
            'identifier' => $identifier,
            'secret' => $secret,
            'identity_id' => $identityId,
            'redirect_uris' => json_encode($redirectUris),
            'app_name' => $appName,
            'revoked' => 1
        ];

        return $this->clientProvider->store($data);
    }

    protected function isGrantSupported(ClientModel $client, ?string $grant): bool
    {
        if (null === $grant || empty($grants = $client->getGrants())) {
            return true;
        }

        return in_array($grant, $grants);
    }

    protected function checkCredentials(ClientModel $clientModel, ?string $secret): bool
    {
        if (!$secret) {
            return true;
        }

        return hash_equals($clientModel->getSecret(), $secret);
    }
}