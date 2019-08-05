<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Models;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns\HasRevoke;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithRefreshToken;

class RefreshTokenModel extends Model implements WithRefreshToken
{
    use HasRevoke;

    /**
     * @var string
     */
    protected $table = 'oauth2_refresh_token';

    /**
     * @var string
     */
    protected $primaryKey = 'identifier';

    /**
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = [
        'identifier', 'access_token', 'expires_at'
    ];

    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(AccessTokenModel::class, 'identifier', 'identifier');
    }

    public function getId(): string
    {
        return $this->getKey();
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