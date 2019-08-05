<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns\HasRevoke;
use MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns\HasScopes;
use MerchantOfComplexity\Oauth\Infrastructure\Models\Concerns\HasTokens;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithAccessToken;

class AccessTokenModel extends Model implements WithAccessToken
{
    use HasTokens, HasScopes, HasRevoke;

    /**
     * Fqcn identity model
     *
     * @var null|string
     */
    static public $identityModel = null;

    /**
     * @var string
     */
    protected $table = 'oauth2_access_token';

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
        'identifier', 'identity_id', 'client_id',
        'scopes', 'expires_at'
    ];
}