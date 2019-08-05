<?php

namespace MerchantOfComplexity\Oauth\Support\Transformer;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\League\Entity\Scope;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer as Transformer;

class ScopeTransformer implements Transformer
{
    public function toModel(ScopeEntityInterface $scope): ScopeInterface
    {
        return new ScopeModel($scope->getIdentifier());
    }

    public function toModelArray(array $scopes): array
    {
        return array_map(function (Scope $scope): ScopeInterface {
            return $this->toModel($scope);
        }, $scopes);
    }

    public function toLeague(ScopeInterface $scope): Scope
    {
        $scopeEntity = new Scope();

        $scopeEntity->setIdentifier($scope->toString());

        return $scopeEntity;
    }

    public function toLeagueArray(array $scopes): array
    {
        return array_map(function (ScopeInterface $scope): Scope {
            return $this->toLeague($scope);
        }, $scopes);
    }

    public function toStringArray(array $scopes): array
    {
        return array_map(function ($scope) {
            if ($scope instanceof Scope) {
                return $scope->getIdentifier();
            }

            return (string)$scope;

        }, $scopes);
    }
}