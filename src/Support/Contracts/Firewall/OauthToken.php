<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Firewall;

use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\Tokenable;
use Psr\Http\Message\ServerRequestInterface;

interface OauthToken extends Tokenable
{
    /**
     * Return implementation of http message request
     *
     * @return ServerRequestInterface
     */
    public function getServerRequest(): ServerRequestInterface;
}