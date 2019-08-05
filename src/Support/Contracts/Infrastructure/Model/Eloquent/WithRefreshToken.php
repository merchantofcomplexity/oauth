<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\RefreshTokenInterface;

interface WithRefreshToken extends RefreshTokenInterface
{
    public function accessToken(): BelongsTo;
}