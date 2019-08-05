<?php

namespace MerchantOfComplexity\Oauth\Support\Value;

use MerchantOfComplexity\Authters\Support\Contract\Value\ClearCredentials;
use MerchantOfComplexity\Authters\Support\Contract\Value\Value;
use MerchantOfComplexity\Authters\Support\Exception\Assert;

class CodeChallenge implements ClearCredentials
{
    /**
     * @var string
     */
    private $challenge;

    protected function __construct(string $challenge)
    {
        $this->challenge = $challenge;
    }

    public static function nextIdentity(): self
    {
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');

        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    public static function fromString($challenge): self
    {
        Assert::notBlank($challenge);
        Assert::length($challenge, 43);

        return new self($challenge);
    }

    public function sameValueAs(Value $aValue): bool
    {
        return $aValue instanceof $this && $this->getValue() === $aValue->getValue();
    }

    public function getValue(): string
    {
        return $this->challenge;
    }
}