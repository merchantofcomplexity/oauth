<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use MerchantOfComplexity\Authters\Application\Http\Middleware\Authentication;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\LocalToken;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use MerchantOfComplexity\Oauth\Infrastructure\AccessToken\AccessTokenProvider;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientProvider;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;
use MerchantOfComplexity\Oauth\Support\ConvertPsrResponses;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class OauthAuthorization extends Authentication
{
    use ConvertPsrResponses;

    /**
     * @var AuthorizationServer
     */
    private $authorizationServer;

    /**
     * @var ClientProvider
     */
    private $clientProvider;

    /**
     * @var AccessTokenProvider
     */
    private $accessTokenProvider;

    /**
     * @var OauthUserTransformer
     */
    private $userTransformer;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    public function __construct(AuthorizationServer $authorizationServer,
                                ClientProvider $clientProvider,
                                AccessTokenProvider $accessTokenProvider,
                                OauthUserTransformer $userTransformer,
                                ScopeTransformer $scopeTransformer,
                                HttpMessageFactoryInterface $httpMessageFactory,
                                ResponseFactory $responseFactory)
    {
        $this->authorizationServer = $authorizationServer;
        $this->clientProvider = $clientProvider;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->userTransformer = $userTransformer;
        $this->scopeTransformer = $scopeTransformer;
        $this->httpMessageFactory = $httpMessageFactory;
        $this->responseFactory = $responseFactory;
    }

    protected function processAuthentication(Request $request): ?Response
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);
        $psrResponse = $this->httpMessageFactory->createResponse(new Response(''));

        try {
            $authorizationRequest = $this->authorizationServer->validateAuthorizationRequest($psrRequest);

            $userIdentity = $this->extractTokenIdentity();

            if (!$this->hasValidAuthorization($authorizationRequest->getClient(), $userIdentity)) {
                return $this->buildResponseView($authorizationRequest, $userIdentity, $psrRequest);
            }

            return $this->convertResponse(
                $this->approvedAuthenticationRequest($authorizationRequest, $userIdentity, $psrResponse)
            );
        } catch (OAuthServerException $exception) {
            return $this->convertResponse(
                $exception->generateHttpResponse($psrResponse)
            );
        }
    }

    protected function approvedAuthenticationRequest(AuthorizationRequest $authorizationRequest,
                                                     Identity $identity,
                                                     ResponseInterface $response): ResponseInterface
    {
        $authorizationRequest->setUser(($this->userTransformer)($identity));

        $authorizationRequest->setAuthorizationApproved(true);

        return $this->authorizationServer->completeAuthorizationRequest($authorizationRequest, $response);
    }

    protected function hasValidAuthorization(ClientEntityInterface $client, Identity $identity): bool
    {
        $clientModel = $this->clientProvider->clientOfIdentifier($client->getIdentifier());

        if ($token = $this->accessTokenProvider->findValidToken($clientModel, $identity)) {
            return true;
        }

        return false;
    }

    protected function extractTokenIdentity(): Identity
    {
        if (!$token = $this->guard->storage()->getToken()) {
            throw new AuthenticationException("login");
        }

        if (!$token instanceof LocalToken) {
            throw new AuthenticationException("local token only");
        }

        return $token->getIdentity();
    }

    protected function buildResponseView(AuthorizationRequest $authorizationRequest,
                                         Identity $identity,
                                         ServerRequestInterface $request): Response
    {
        // fixMe
        return $this->responseFactory->view('oauth.authorize', [
            'authRequest' => $authorizationRequest,
            'request' => $request,
            'identity' => $identity
        ]);
    }

    protected function requireAuthentication(Request $request): bool
    {
        return $request->is('oauth/authorize*');
    }
}