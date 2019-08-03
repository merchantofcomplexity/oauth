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

class ClientModel extends Model
{
    use HasGrants, HasRedirectUri, HasRevoke;

    protected $table = 'oauth2_client';

    protected $primaryKey = 'identifier';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'identifier', 'secret', 'identity_id', 'app_name', 'revoked'
    ];

    protected $hidden = 'secret';

    public function identity(): BelongsTo
    {
        return $this->belongsTo(OauthIdentityModel::class, 'id', 'identity_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(AccessTokenModel::class, 'client_id', 'identity_id');
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