<?php

namespace MerchantOfComplexity\Oauth\Support\Transformer;

use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\League\Entity\Scope;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer as Transformer;

class ScopeTransformer implements Transformer
{
    public function toModel(Scope $scope): ScopeModel
    {
        return new ScopeModel($scope->getIdentifier());
    }

    public function toModelArray(Scope ...$scopes): array
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

    public function toLeagueArray(ScopeModel ...$scopes): array
    {
        return array_map(function (ScopeModel $scope): Scope {
            return $this->toLeague($scope);
        }, $scopes);
    }
}