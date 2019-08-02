<?php

namespace MerchantOfComplexity\Oauth\Support;

use BadMethodCallException;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeProvider;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;

/**
 * @method ScopeTransformer toLeague(ScopeModel $scope)
 * @method ScopeTransformer toLeagueArray(ScopeModel $scope)
 * @method ScopeTransformer toModel(ScopeEntityInterface $scope)
 * @method ScopeTransformer toModelArray(ScopeEntityInterface $scope)
 */
class ScopeBuilder
{
    /**
     * @var ScopeProvider
     */
    private $scopeProvider;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    public function __construct(ScopeProvider $scopeProvider, ScopeTransformer $scopeTransformer)
    {
        $this->scopeProvider = $scopeProvider;
        $this->scopeTransformer = $scopeTransformer;
    }

    /**
     * @param ScopeModel ...$scopes
     * @return ScopeModel[]
     */
    public function filterScopes(ScopeModel ...$scopes): array
    {
        $availableScopes = [];

        foreach ($scopes as $scope) {
            if ($this->isScopeAvailable((string)$scope)) {
                $availableScopes[] = clone $scope;
            }
        }

        return $availableScopes;
    }

    public function isScopeAvailable(string $scope): bool
    {
        return null !== $this->scopeProvider->scopeOfIdentifier($scope);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this, $name)) {
            return call_user_func_array([$this->scopeTransformer, $name], $arguments);
        }

        throw new BadMethodCallException("Method $name not found in class " . __CLASS__);
    }
}