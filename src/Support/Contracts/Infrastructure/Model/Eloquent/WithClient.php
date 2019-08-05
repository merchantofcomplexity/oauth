<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;

interface WithClient extends ClientInterface
{
    public function identity(): BelongsTo;

    public function tokens(): HasMany;

    public function authCodes(): HasMany;
}