<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Transformer;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\League\Entity\Scope;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;

interface ScopeTransformer
{
    /**
     * @param ScopeEntityInterface $scope
     * @return ScopeInterface
     */
    public function toModel(ScopeEntityInterface $scope): ScopeInterface;

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return array
     */
    public function toModelArray(array $scopes): array;

    /**
     * @param ScopeInterface $scope
     * @return Scope
     */
    public function toLeague(ScopeInterface $scope): Scope;

    /**
     * @param ScopeInterface[] $scopes
     * @return Scope[]
     */
    public function toLeagueArray(array $scopes): array;

    /**
     * @param string|ScopeInterface[]|Scope[] $scopes
     * @return string[]
     */
    public function toStringArray(array $scopes): array;
}