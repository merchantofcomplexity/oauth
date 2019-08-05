<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns;

trait HasRevoke
{
    public function revoke(): void
    {
        if ($this->exists && !$this->isRevoked()) {
            $this['revoked'] = 1;

            $this->save();
        }
    }

    public function isRevoked(): bool
    {
        return $this['revoked'] === 1;
    }
}