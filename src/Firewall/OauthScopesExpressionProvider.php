<?php

namespace MerchantOfComplexity\Oauth\Firewall;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class OauthScopesExpressionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('has_scope', function ($role) {
                return sprintf('in_array(%s, $roles)', $role);
            }, function (array $variables, $role) {
                return in_array($role, $variables['roles']);
            }),
        ];
    }
}