<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\League\Repository;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface as LeagueClientRepository;
use MerchantOfComplexity\Authters\Support\Contract\Value\IdentifierValue;
use MerchantOfComplexity\Oauth\Support\Value\OauthIdentifier;
use MerchantOfComplexity\Oauth\Support\Value\OauthSecret;

interface ClientRepositoryInterface extends LeagueClientRepository
{
    /**
     * Create new oauth client application
     *
     * @param IdentifierValue $identityId
     * @param OauthIdentifier $oauthIdentifier
     * @param OauthSecret $oauthSecret
     * @param string $appName
     * @param string[] $redirectUris
     */
    public function createClient(IdentifierValue $identityId,
                                 OauthIdentifier $oauthIdentifier,
                                 OauthSecret $oauthSecret,
                                 string $appName,
                                 array $redirectUris): void;

}