<?php

namespace MerchantOfComplexity\Oauth\Http\Request;

use Illuminate\Http\Request;
use MerchantOfComplexity\Authters\Support\Contract\Application\Http\Request\AuthenticationRequest;

final class AuthorizationRequestMatcher implements AuthenticationRequest
{
    /**
     * @var string
     */
    private $authorizeUri;

    public function __construct(string $authorizeUri = 'oauth/authorize*')
    {
        $this->authorizeUri = $authorizeUri;
    }

    public function match(Request $request): bool
    {
        return $request->is($this->authorizeUri);
    }

    public function extractCredentials(Request $request)
    {
       return null;
    }
}