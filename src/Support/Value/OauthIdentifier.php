<?php

namespace MerchantOfComplexity\Oauth\Support\Value;

use MerchantOfComplexity\Authters\Support\Contract\Value\IdentifierValue;
use MerchantOfComplexity\Authters\Support\Contract\Value\Value;
use MerchantOfComplexity\Authters\Support\Exception\Assert;

final class OauthIdentifier implements IdentifierValue
{
    /**
     * @var string
     */
    private $id;

    protected function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function nextIdentity(): self
    {
        return new self(hash('md5', random_bytes(16)));
    }

    public static function fromString($identifier): self
    {
        Assert::notBlank($identifier);
        Assert::length($identifier, 32);

        return new self($identifier);
    }

    public function identify(): string
    {
        return $this->id;
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