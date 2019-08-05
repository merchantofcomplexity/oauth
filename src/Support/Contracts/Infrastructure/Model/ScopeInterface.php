<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model;

interface ScopeInterface
{
    public function toString(): string;

    public function __toString(): string;
}