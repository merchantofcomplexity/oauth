<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;

trait HasScopes
{
    /**
     * @return ScopeModel[]
     */
    public function getScopes(): array
    {
        if (is_string($this['scopes'])) {
            return json_decode($this['scopes']);
        }

        return $this['scopes'] ?? [];
    }
}