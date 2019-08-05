<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\AccessToken;

use Illuminate\Database\Eloquent\Model;
use MerchantOfComplexity\Oauth\Infrastructure\HasRevoke;
use MerchantOfComplexity\Oauth\Infrastructure\HasScopes;
use MerchantOfComplexity\Oauth\Infrastructure\HasTokenModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithAccessToken;

class AccessTokenModel extends Model implements WithAccessToken
{
    use HasTokenModel, HasScopes, HasRevoke;

    protected $table = 'oauth2_access_token';

    protected $primaryKey = 'identifier';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'identifier', 'identity_id', 'client_id',
        'scopes', 'expires_at'
    ];
}