<?php

namespace MerchantOfComplexity\Oauth\Infrastructure;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;

trait HasTokenModel
{
    protected $primaryKey = ' identifier';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'identifier', 'identity_id', 'client_id',
        'scopes', 'expires_at'
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(ClientModel::class, 'identifier', 'client_id');
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(OauthIdentityModel::class, 'identifier', 'client_id');
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