<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;

trait HasTokenModel
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

    public function getExpiry(): DateTimeInterface
    {
        return new \DateTimeImmutable($this['expires_at'], new \DateTimeZone('UTC'));
    }
}