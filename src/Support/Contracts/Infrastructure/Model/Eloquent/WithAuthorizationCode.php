<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AuthorizationCodeInterface;

interface WithAuthorizationCode extends AuthorizationCodeInterface
{
    public function client(): BelongsTo;

    public function identity(): BelongsTo;
}