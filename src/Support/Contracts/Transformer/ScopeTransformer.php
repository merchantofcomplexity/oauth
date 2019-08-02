<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Transformer;

use League\OAuth2\Server\Entities\ScopeEntityInterface;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\League\Entity\Scope;

interface ScopeTransformer
{
    /**
     * @param ScopeEntityInterface $scope
     * @return ScopeModel
     */
    public function toModel(ScopeEntityInterface $scope): ScopeModel;

    /**
     * @param ScopeEntityInterface[] $scopes
     * @return array
     */
    public function toModelArray(ScopeEntityInterface ...$scopes): array;

    /**
     * @param ScopeModel $scope
     * @return Scope
     */
    public function toLeague(ScopeModel $scope): Scope;

    /**
     * @param ScopeModel[] $scopes
     * @return Scope[]
     */
    public function toLeagueArray(ScopeModel ...$scopes): array;
}