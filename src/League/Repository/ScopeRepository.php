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

    /**
     * Given a client, grant type and optional user identifier validate the set of scopes requested are valid and optionally
     * append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string $grantType
     * @param ClientEntityInterface $clientEntity
     * @param null|string $userIdentifier
     *
     * @return ScopeEntityInterface[]
     */
    public function finalizeScopes(array $scopes,
                                   $grantType,
                                   ClientEntityInterface $clientEntity,
                                   $userIdentifier = null): array
    {
        return [];
    }
}