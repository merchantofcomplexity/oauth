<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AuthorizationCodeInterface;

interface WithAuthorizationCode extends AuthorizationCodeInterface
{
    /**
     * Return client relation of authorization code
     *
     * @return BelongsTo
     */
    public function client(): BelongsTo;

    /**
     * Return identity relation of access token
     *
     * @return BelongsTo
     */
    public function identity(): BelongsTo;
}