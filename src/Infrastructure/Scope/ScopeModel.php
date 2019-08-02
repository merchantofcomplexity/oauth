<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\Scope;

class ScopeModel
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