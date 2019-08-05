<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use MerchantOfComplexity\Authters\Application\Http\Middleware\Authentication;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\LocalToken;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\RecallerToken;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationServiceFailure;
use MerchantOfComplexity\Oauth\Infrastructure\AccessToken\AccessTokenProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientProvider;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\ConvertPsrResponses;
use MerchantOfComplexity\Oauth\Support\ScopeManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
     * @var ScopeManager
     */
    protected $scopeManager;

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
                                ProvideClient $clientProvider,
                                ProvideAccessToken $accessTokenProvider,
                                ScopeManager $scopeManager,
                                OauthUserTransformer $userTransformer,
                                ResponseFactory $responseFactory,
                                HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->authorizationServer = $authorizationServer;
        $this->clientProvider = $clientProvider;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->scopeManager = $scopeManager;
        $this->userTransformer = $userTransformer;
        $this->responseFactory = $responseFactory;
        $this->httpMessageFactory = $httpMessageFactory;
    }

    protected function processAuthentication(Request $request): ?Response
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);

        $psrResponse = $this->httpMessageFactory->createResponse(new Response(''));

        try {
            return $this->authorizeRequest($this->extractTokenIdentity(), $request, $psrRequest, $psrResponse);
        } catch (OAuthServerException $exception) {
            throw $exception;
            return $this->convertResponse($exception->generateHttpResponse($psrResponse));
        }
    }

    /**
     * @param Identity $identity
     * @param Request $request
     * @param ServerRequestInterface $psrRequest
     * @param ResponseInterface $psrResponse
     * @return Response
     * @throws OAuthServerException
     */
    abstract protected function authorizeRequest(Identity $identity,
                                                 Request $request,
                                                 ServerRequestInterface $psrRequest,
                                                 ResponseInterface $psrResponse): Response;

    protected function approveAuthorizationRequest(AuthorizationRequest $authRequest,
                                                   Identity $identity,
                                                   ResponseInterface $psrResponse): Response
    {
        $authRequest->setUser(($this->userTransformer)($identity));

        $authRequest->setAuthorizationApproved(true);

        return $this->convertResponse(
            $this->authorizationServer->completeAuthorizationRequest($authRequest, $psrResponse)
        );
    }

    protected function extractAuthorizationRequestFromSession(Request $request): AuthorizationRequest
    {
        $authRequest = $request->session()->get(self::SESSION_OAUTH_KEY);

        if (!$authRequest instanceof AuthorizationRequest) {
            throw new AuthenticationException("Oauth authorization request failed");
        }

        return $authRequest;
    }

    protected function extractTokenIdentity(): Identity
    {
        if (!$token = $this->guard->storage()->getToken()) {
            throw new AuthenticationException("You must login first");
        }

        if (!$token instanceof LocalToken && !$token instanceof RecallerToken) {
            throw new AuthenticationServiceFailure(
                "only local and recaller token are allowed for oauth"
            );
        }

        return $token->getIdentity();
    }
}