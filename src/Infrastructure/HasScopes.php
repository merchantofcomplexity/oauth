<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

trait HasScopes
{
    /**
     * @return string[]
     */
    public function getScopes(): array
    {
        if (is_string($this['scopes'])) {
            return json_decode($this['scopes']);
        }

        return $this['scopes'];
    }
}