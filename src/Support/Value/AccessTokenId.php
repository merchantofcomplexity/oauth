<?php

namespace MerchantOfComplexity\Oauth\Support\Value;

use MerchantOfComplexity\Authters\Support\Contract\Value\ClearCredentials;
use MerchantOfComplexity\Authters\Support\Contract\Value\Value;

final class AccessTokenId implements ClearCredentials
{
    /**
     * @var string
     */
    private $token;

    protected function __construct(string $token)
    {
        $this->token = $token;
    }

    public static function fromString($token): self
    {
        return new self($token);
    }

    public function getValue(): string
    {
        return $this->token;
    }

    public function sameValueAs(Value $aValue): bool
    {
        return $aValue instanceof $this && $this->getValue() === $aValue->getValue();
    }
}