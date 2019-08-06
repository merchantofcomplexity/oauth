<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;

interface WithClient extends ClientInterface
{
    /**
     * Return identity relation of client
     *
     * @return BelongsTo
     */
    public function identity(): BelongsTo;

    /**
     * Return access tokens relation of client
     *
     * @return HasMany
     */
    public function tokens(): HasMany;

    /**
     * Return authorization codes relation of client
     *
     * @return HasMany
     */
    public function authCodes(): HasMany;
}