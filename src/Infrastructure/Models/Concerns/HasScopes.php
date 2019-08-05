<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns;

use MerchantOfComplexity\Oauth\Infrastructure\Models\ScopeModel;

trait HasScopes
{
    public function getScopes(): array
    {
        if (is_string($this['scopes'])) {
            return array_map(function(?string $scope){
                return new ScopeModel($scope);
            }, json_decode($this['scopes']));
        }

        return $this['scopes'] ?? [];
    }
}