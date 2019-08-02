<?php

namespace MerchantOfComplexity\Oauth\Http\Request;

use Illuminate\Http\Request;
use MerchantOfComplexity\Authters\Support\Contract\Application\Http\Request\AuthenticationRequest;

final class AuthorizationRequestMatcher implements AuthenticationRequest
{
    /**
     * @var string
     */
    private $authorizeRouteName;

    public function __construct(string $authorizeRouteName = 'oauth.authorization')
    {
        $this->authorizeRouteName = $authorizeRouteName;
    }

    public function match(Request $request): bool
    {
        return $request->route()->getName() === $this->authorizeRouteName;
    }

    public function extractCredentials(Request $request)
    {
       return null;
    }
}