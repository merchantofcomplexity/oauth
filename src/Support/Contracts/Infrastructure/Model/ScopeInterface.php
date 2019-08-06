<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model;

interface ScopeInterface
{
    /**
     * return scope as string
     *
     * @return string
     */
    public function toString(): string;

    /**
     * @return string
     */
    public function __toString(): string;
}