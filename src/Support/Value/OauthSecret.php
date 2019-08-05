<?php

namespace MerchantOfComplexity\Oauth\Support\Value;

use MerchantOfComplexity\Authters\Support\Contract\Value\ClearCredentials;
use MerchantOfComplexity\Authters\Support\Contract\Value\Value;
use MerchantOfComplexity\Authters\Support\Exception\Assert;

final class OauthSecret implements ClearCredentials
{
    /**
     * @var string
     */
    private $secret;

    protected function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public static function nextIdentity(): self
    {
        return new self(hash('sha512', random_bytes(32)));
    }

    public static function fromString($secret): self
    {
        Assert::notBlank($secret);
        Assert::length($secret, 128);

        return new self($secret);
    }

    public function identify(): string
    {
        return $this->secret;
    }

    public function sameValueAs(Value $aValue): bool
    {
        return $aValue instanceof $this && $this->identify() === $aValue->identify();
    }

    public function getValue(): string
    {
        return $this->identify();
    }
}