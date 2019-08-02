<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Client;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MerchantOfComplexity\Oauth\Infrastructure\AccessToken\AccessTokenModel;
use MerchantOfComplexity\Oauth\Infrastructure\HasGrants;
use MerchantOfComplexity\Oauth\Infrastructure\HasRedirectUri;
use MerchantOfComplexity\Oauth\Infrastructure\HasScopes;
use MerchantOfComplexity\Oauth\Infrastructure\OauthIdentityModel;

class ClientModel extends Model
{
    use HasScopes, HasGrants, HasRedirectUri;

    protected $table = 'oauth2_client';

    public $incrementing = false;

    protected $primaryKey = ' identifier';

    protected $keyType = 'string';

    protected $fillable = [
        'identifier', 'secret', 'identity_id', 'app_name'
    ];

    protected $hidden = 'secret';

    public function identity(): BelongsTo
    {
        return $this->belongsTo(OauthIdentityModel::class, 'id', 'identity_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(AccessTokenModel::class, 'client_id', 'identifier');
    }

    public function isActive(): bool
    {
        return $this['active'] === 1;
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