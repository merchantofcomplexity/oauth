<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Firewall;

use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\Tokenable;
use Psr\Http\Message\ServerRequestInterface;

interface OauthToken extends Tokenable
{
    public function getServerRequest(): ServerRequestInterface;
}