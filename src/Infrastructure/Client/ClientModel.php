<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MerchantOfComplexity\Oauth\Infrastructure\AccessToken\AccessTokenModel;
use MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode\AuthCodeModel;
use MerchantOfComplexity\Oauth\Infrastructure\HasGrants;
use MerchantOfComplexity\Oauth\Infrastructure\HasRedirectUri;
use MerchantOfComplexity\Oauth\Infrastructure\HasRevoke;
use MerchantOfComplexity\Oauth\Infrastructure\OauthIdentityModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithClient;

class ClientModel extends Model implements WithClient
{
    use HasGrants, HasRedirectUri, HasRevoke;

    /**
     * Fqcn identity model
     *
     * @var null|string
     */
    static public $identityModel = null;

    /**
     * @var string
     */
    protected $table = 'oauth2_client';

    /**
     * @var string
     */
    protected $primaryKey = 'identifier';

    /**
     * @var bool
     */
    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @var array
     */
    protected $fillable = [
        'identifier', 'secret', 'identity_id', 'app_name', 'redirect_uris', 'revoked'
    ];

    /**
     * @var string
     */
    protected $hidden = 'secret';

    public function identity(): BelongsTo
    {
        return $this->belongsTo(static::$identityModel, 'id', 'identity_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(AccessTokenModel::class, 'client_id', 'identifier');
    }

    public function authCodes(): HasMany
    {
        return $this->hasMany(AuthCodeModel::class, 'client_id', 'identifier');
    }

    public function getId(): string
    {
        return $this->getKey();
    }

    public function getSecret(): string
    {
        return $this['secret'];
    }

    public function getAppName(): string
    {
        return $this['name'];
    }
}