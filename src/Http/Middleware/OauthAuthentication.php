<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Http\Request;
use MerchantOfComplexity\Authters\Application\Http\Middleware\Authentication;
use MerchantOfComplexity\Authters\Support\Contract\Firewall\Key\ContextKey;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use MerchantOfComplexity\Oauth\Exception\InsufficientScopes;
use MerchantOfComplexity\Oauth\Exception\OauthException;
use MerchantOfComplexity\Oauth\Firewall\OauthToken;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class OauthAuthentication extends Authentication
{
    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /**
     * @var ContextKey
     */
    private $contextKey;

    public function __construct(HttpMessageFactoryInterface $httpMessageFactory, ContextKey $contextKey)
    {
        $this->httpMessageFactory = $httpMessageFactory;
        $this->contextKey = $contextKey;
    }

    protected function processAuthentication(Request $request): ?Response
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);

        try {
            $token = $this->guard->authenticateToken(
                new OauthToken($psrRequest, $this->contextKey, null)
            );
        } catch (AuthenticationException $exception) {
            throw OauthException::reason($exception->getMessage(), $exception);
        }

        if (!$this->isAccessRoutesGranted($request, $token)) {
            throw InsufficientScopes::withToken($token);
        }

        $this->guard->storage()->setToken($token);

        return null;
    }

    protected function isAccessRoutesGranted(Request $request, OauthToken $token): bool
    {
        // fixMe only if we use access control
        $routeScopes = $request->attributes->get('oauth_route_scopes', []);

        if (!$routeScopes) {
            return true;
        }

        $tokenScopes = $token->getServerRequest()->getAttribute('oauth_scopes');

        return empty(array_diff($routeScopes, $tokenScopes));
    }

    protected function requireAuthentication(Request $request): bool
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);

        return $psrRequest->hasHeader('Authorization');
    }
}