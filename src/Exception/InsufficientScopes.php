<?php

namespace MerchantOfComplexity\Oauth\Exception;

use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\Tokenable;

class InsufficientScopes extends OauthException
{
    public static function withToken(Tokenable $token): self
    {
        $exception = new self('The token has insufficient scopes.', 403);

        // fixMe $exception->setToken($token);

        return $exception;
    }
}