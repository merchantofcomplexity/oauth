<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeProvider;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;

final class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var ScopeProvider
     */
    private $scopeProvider;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    public function __construct(ClientProvider $clientProvider,
                                ScopeProvider $scopeProvider,
                                ScopeTransformer $scopeTransformer)
    {
        $this->clientProvider = $clientProvider;
        $this->scopeProvider = $scopeProvider;
        $this->scopeTransformer = $scopeTransformer;
    }

    public function getScopeEntityByIdentifier($identifier): ?ScopeEntityInterface
    {
        $scope = $this->scopeProvider->scopeOfIdentifier($identifier);

        if (!$scope) {
            return null;
        }

        return $this->scopeTransformer->toLeague($scope);
    }

    public function finalizeScopes(array $scopes,
                                   $grantType,
                                   ClientEntityInterface $clientEntity,
                                   $userIdentifier = null): array
    {
        // checkMe default scopes
        return $scopes;
    }
}