<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MerchantOfComplexity\Oauth\Infrastructure\Models\ClientModel;

trait HasTokens
{
    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'client_id', 'identifier');
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(static::$identityModel, 'identity_id', 'id');
    }

    public function getId(): string
    {
        return $this->getKey();
    }

    public function getIdentityId(): ?string
    {
        return $this['identity_id'];
    }

    /**
     * @return DateTimeInterface
     * @throws Exception
     */
    public function getExpiry(): DateTimeInterface
    {
        return new DateTimeImmutable($this['expires_at'], new DateTimeZone('UTC'));
    }
}