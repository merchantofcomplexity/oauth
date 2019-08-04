<?php

namespace MerchantOfComplexity\Oauth\Support\Transformer;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\League\Entity\Scope;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer as Transformer;

class ScopeTransformer implements Transformer
{
    public function toModel(ScopeEntityInterface $scope): ScopeModel
    {
        return new ScopeModel($scope->getIdentifier());
    }

    public function toModelArray(array $scopes): array
    {
        return array_map(function (Scope $scope): ScopeModel {
            return $this->toModel($scope);
        }, $scopes);
    }

    public function toLeague(ScopeModel $scope): Scope
    {
        $scopeEntity = new Scope();

        $scopeEntity->setIdentifier((string)$scope);

        return $scopeEntity;
    }

    public function toLeagueArray(array $scopes): array
    {
        return array_map(function (ScopeModel $scope): Scope {
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