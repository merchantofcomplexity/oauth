<?php

namespace MerchantOfComplexity\Oauth\Firewall;

use Illuminate\Support\Str;
use MerchantOfComplexity\Authters\Guard\Authorization\Voter\Voter;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\Tokenable;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\TrustResolver;
use MerchantOfComplexity\Oauth\Support\ScopeManager;

final class OauthScopesVoter extends Voter
{
    const ALL = 'oauth_scopes_all:';
    const ANY = 'oauth_scopes_any:';

    /**
     * @var ScopeManager
     */
    private $scopeManager;

    /**
     * @var TrustResolver
     */
    private $trustResolver;

    public function __construct(ScopeManager $scopeManager, TrustResolver $trustResolver)
    {
        $this->scopeManager = $scopeManager;
        $this->trustResolver = $trustResolver;
    }

    protected function supports(string $attribute, object $subject = null): bool
    {
        return $attribute === self::ALL || $attribute === self::ANY;
    }

    protected function voteOn(string $attribute, Tokenable $token, object $subject = null): bool
    {
        if ($this->trustResolver->isFullyAuthenticated($token)) {
            return false;
        }

        $scopes = $this->explodeScopesAttribute($attribute);

        if (!$scopes) {
            return false;
        }

        $tokenRoles = $token->getRoleNames();

        if ($attribute === self::ALL) {
            return $this->checkForAllScopes($scopes, $tokenRoles);
        }

        return $this->checkForAnyScopes($scopes, $tokenRoles);
    }

    protected function checkForAllScopes(array $scopes, array $tokenRoles): bool
    {
        foreach ($scopes as $scope) {
            if (!in_array($scope, $tokenRoles)) {
                return false;
            }
        }

        return true;
    }

    protected function checkForAnyScopes(array $scopes, array $tokenRoles): bool
    {
        foreach ($scopes as $scope) {
            if (in_array($scope, $tokenRoles)) {
                return true;
            }
        }

        return false;
    }

    protected function explodeScopesAttribute(string $attribute): array
    {
        $stringScopes = substr($attribute, strlen(self::ALL));

        if (Str::endsWith($stringScopes, ',')) {
            $stringScopes .= ',';
        }

        return array_filter(
            array_map(function (string $scope) {
                return trim($scope);
            }, explode(',', $stringScopes))
        );
    }
}