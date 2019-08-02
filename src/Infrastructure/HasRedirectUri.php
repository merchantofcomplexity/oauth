<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

trait HasRedirectUri
{
    /**
     * @return string[]
     */
    public function getRedirectUris(): array
    {
        if (is_string($this['redirect_uris'])) {
            return json_decode($this['redirect_uris']);
        }

        return $this['redirect_uris'] ?? [];
    }
}