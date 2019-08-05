<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

trait HasGrants
{
    public function getGrants(): array
    {
        if (is_string($this['grants'])) {
            return json_decode($this['grants']);
        }

        return $this['grants'] ?? [];
    }
}