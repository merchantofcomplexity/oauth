<?php

namespace MerchantOfComplexity\Oauth\Support\Value;

use Illuminate\Support\Str;
use MerchantOfComplexity\Authters\Support\Contract\Value\Value;
use MerchantOfComplexity\Authters\Support\Exception\Assert;

final class QueryState implements Value
{
    const LENGTH = 40;

    /**
     * @var string
     */
    private $state;

    protected function __construct(string $state)
    {
        $this->state = $state;
    }

    public static function nextIdentity(): self
    {
        return new self(Str::random(self::LENGTH));
    }

    public static function fromString($state): self
    {
        Assert::string($state);
        Assert::length($state, self::LENGTH);

        return new self($state);
    }

    public function sameValueAs(Value $aValue): bool
    {
        return $aValue instanceof $this && $this->getValue() === $aValue->getValue();
    }

    public function getValue(): string
    {
        return $this->state;
    }
}