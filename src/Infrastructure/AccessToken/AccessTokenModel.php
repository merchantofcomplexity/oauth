<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\AccessToken;

use Illuminate\Database\Eloquent\Model;
use MerchantOfComplexity\Oauth\Infrastructure\HasRevoke;
use MerchantOfComplexity\Oauth\Infrastructure\HasScopes;
use MerchantOfComplexity\Oauth\Infrastructure\HasTokenModel;

class AccessTokenModel extends Model
{
    use HasTokenModel, HasScopes, HasRevoke;

    protected $table = 'oauth2_access_token';
}