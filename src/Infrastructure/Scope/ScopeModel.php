<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Scope;

use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;

class ScopeModel implements ScopeInterface
{
    /**
     * @var string
     */
    private $scope;

    public function __construct(string $scope)
    {
        $this->scope = $scope;
    }

    public function toString(): string
    {
        return $this->scope;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}