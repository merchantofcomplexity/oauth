<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;

use MerchantOfComplexity\Authters\Support\Contract\Value\IdentifierValue;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;
use MerchantOfComplexity\Oauth\League\Entity\Client;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use MerchantOfComplexity\Oauth\Support\Contracts\League\Repository\ClientRepositoryInterface;
use MerchantOfComplexity\Oauth\Support\Value\OauthIdentifier;
use MerchantOfComplexity\Oauth\Support\Value\OauthSecret;

final class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @var ProvideClient
     */
    private $clientProvider;

    public function __construct(ProvideClient $clientProvider)
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

    public function createClient(IdentifierValue $identityId,
                                 OauthIdentifier $oauthIdentifier,
                                 OauthSecret $oauthSecret,
                                 string $appName,
                                 array $redirectUris): void
    {
        // app name unique in provider with identity
        // valid redirectUris, make a vo with validation

        $data = [
            'identifier' => $oauthIdentifier->identify(),
            'secret' => $oauthSecret->getValue(),
            'identity_id' => $identityId->identify(),
            'redirect_uris' => json_encode($redirectUris),
            'app_name' => $appName,
            'revoked' => 0
        ];

        $this->clientProvider->store($data);
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

        if(!$clientModel->exists){
            return false;
        }

        return hash_equals($clientModel->getSecret(), $secret);
    }
}