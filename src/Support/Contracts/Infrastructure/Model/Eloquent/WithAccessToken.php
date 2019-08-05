<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;
use MerchantOfComplexity\Oauth\Infrastructure\OauthIdentityModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AccessTokenInterface;

interface WithAccessToken extends AccessTokenInterface
{
    public function client(): BelongsTo;

    public function identity(): BelongsTo;
}