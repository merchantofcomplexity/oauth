<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use MerchantOfComplexity\Authters\Application\Http\Middleware\Authentication;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use MerchantOfComplexity\Oauth\Infrastructure\AccessToken\AccessTokenProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Scope\ScopeModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\ConvertPsrResponses;
use MerchantOfComplexity\Oauth\Support\ScopeBuilder;
use MerchantOfComplexity\Oauth\Support\Value\ClientRedirectUri;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class OauthApproval extends Authentication
{
    use ConvertPsrResponses;

    const SESSION_OAUTH_KEY = 'auth_request';

    /**
     * @var AuthorizationServer
     */
    protected $authorizationServer;

    /**
     * @var ClientProvider
     */
    protected $clientProvider;

    /**
     * @var AccessTokenProvider
     */
    protected $accessTokenProvider;

    /**
     * @var ScopeBuilder
     */
    protected $scopeBuilder;

    /**
     * @var OauthUserTransformer
     */
    protected $userTransformer;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var HttpMessageFactoryInterface
     */
    protected $httpMessageFactory;


    public function __construct(AuthorizationServer $authorizationServer,
                                ClientProvider $clientProvider,
                                AccessTokenProvider $accessTokenProvider,
                                ScopeBuilder $scopeBuilder,
                                OauthUserTransformer $userTransformer,
                                ResponseFactory $responseFactory,
                                HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->authorizationServer = $authorizationServer;
        $this->clientProvider = $clientProvider;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->scopeBuilder = $scopeBuilder;
        $this->userTransformer = $userTransformer;
        $this->responseFactory = $responseFactory;
        $this->httpMessageFactory = $httpMessageFactory;
    }

    protected function approveRequest(AuthorizationRequest $authRequest, Identity $identity): AuthorizationRequest
    {
        $this->approveAuthorizationRequest($authRequest, $identity);

        return $authRequest;
    }

    protected function confirmRequest(Request $request, Identity $identity): AuthorizationRequest
    {
        $authRequest = $this->requireAuthorizationRequestFromSession($request);

        $this->approveAuthorizationRequest($authRequest, $identity);

        return $authRequest;
    }

    protected function denyRequest(Request $request): Response
    {
        $authRequest = $this->requireAuthorizationRequestFromSession($request);

        return $this->responseFactory->redirectTo(
            ClientRedirectUri::fromAuthorizationRequest($authRequest, $request)->getValue()
        );
    }

    protected function buildAuthorizationView(AuthorizationRequest $authorizationRequest,
                                              Identity $identity,
                                              Request $request,
                                              ScopeModel ...$scopes): Response
    {
        $request->session()->flash(self::SESSION_OAUTH_KEY, $authorizationRequest);

        // fixMe
        return $this->responseFactory->view('oauth.authorize', [
            'client' => $authorizationRequest->getClient(),
            'request' => $request,
            'identity' => $identity,
            'scopes' => $scopes
        ]);
    }

    protected function approveAuthorizationRequest(AuthorizationRequest $authRequest, Identity $identity): void
    {
        $authRequest->setUser(($this->userTransformer)($identity));

        $authRequest->setAuthorizationApproved(true);
    }

    protected function completeAuthorization(AuthorizationRequest $authRequest, ResponseInterface $response): Response
    {
        return $this->convertResponse(
            $this->authorizationServer->completeAuthorizationRequest($authRequest, $response)
        );
    }

    protected function requireAuthorizationRequestFromSession(Request $request): AuthorizationRequest
    {
        $authRequest = $request->session()->get(self::SESSION_OAUTH_KEY);

        if (!$authRequest instanceof AuthorizationRequest) {
            throw new AuthenticationException("Oauth authorization request failed");
        }

        return $authRequest;
    }
}