<?php

namespace MerchantOfComplexity\Oauth\Exception;

use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use Throwable;

class OauthException extends AuthenticationException
{
    public static function reason(string $message, Throwable $exception = null): self
    {
        return new self($message, 401, $exception);
    }
}