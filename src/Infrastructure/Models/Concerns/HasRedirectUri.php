<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns;

trait HasRedirectUri
{
    public function getRedirectUris(): array
    {
        if (is_string($this['redirect_uris'])) {
            return json_decode($this['redirect_uris']);
        }

        return $this['redirect_uris'] ?? [];
    }
}