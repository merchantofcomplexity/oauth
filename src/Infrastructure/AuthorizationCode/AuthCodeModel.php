<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode;

use Illuminate\Database\Eloquent\Model;
use MerchantOfComplexity\Oauth\Infrastructure\HasRevoke;
use MerchantOfComplexity\Oauth\Infrastructure\HasScopes;
use MerchantOfComplexity\Oauth\Infrastructure\HasTokenModel;

class AuthCodeModel extends Model
{
    use HasTokenModel, HasScopes, HasRevoke;

    protected $table = 'oauth2_authorization_code';
}