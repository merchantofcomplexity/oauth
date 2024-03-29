<?php

namespace MerchantOfComplexity\Oauth\Support;

use BadMethodCallException;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeProvider;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideScope;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;

/**
 * @method ScopeTransformer toLeague(ScopeInterface $scope)
 * @method ScopeTransformer toLeagueArray(ScopeInterface $scope)
 * @method ScopeTransformer toModel(ScopeEntityInterface $scope)
 * @method ScopeTransformer toModelArray(ScopeEntityInterface $scope)
 * @method ScopeTransformer toStringArray($scope)
 */
class ScopeManager
{
    /**
     * @var ProvideScope
     */
    private $scopeProvider;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    public function __construct(ProvideScope $scopeProvider, ScopeTransformer $scopeTransformer)
    {
        $this->scopeProvider = $scopeProvider;
        $this->scopeTransformer = $scopeTransformer;
    }

    /**
     * @param ScopeInterface[] $scopes
     * @return ScopeInterface[]
     */
    public function filterScopes(ScopeInterface ...$scopes): array
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

    public function equalsScopes(array $scopes, array $aScopes): bool
    {
        if ($scopes === $aScopes) {
            return true;
        }

        return empty($this->diffScopes($scopes, $aScopes));
    }

    /**
     * @param array $scopes
     * @param array $aScopes
     * @return string[]
     */
    public function diffScopes(array $scopes, array $aScopes): array
    {
        return array_diff(
            $this->scopeTransformer->toStringArray($scopes),
            $this->scopeTransformer->toStringArray($aScopes)
        );
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this, $name)) {
            $callback = [$this->scopeTransformer, $name];

            if(is_callable($callback)){
                return \Closure::fromCallable($callback)($arguments);
            }
        }

        throw new BadMethodCallException(
            "Method $name not found in class " . get_class($this->scopeTransformer)
        );
    }
}