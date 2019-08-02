<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

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
use MerchantOfComplexity\Oauth\Support\AuthorizationApproval;
use MerchantOfComplexity\Oauth\Support\ConvertPsrResponses;
use Psr\Http\Message\ResponseInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

class OauthAuthorization extends Authentication
{
    use ConvertPsrResponses;

    /**
     * @var AuthorizationApproval
     */
    private $authorizationApproval;

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
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    public function __construct(AuthorizationApproval $authorizationApproval,
                                AuthorizationServer $authorizationServer,
                                ClientProvider $clientProvider,
                                AccessTokenProvider $accessTokenProvider,
                                HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->authorizationApproval = $authorizationApproval;
        $this->authorizationServer = $authorizationServer;
        $this->clientProvider = $clientProvider;
        $this->accessTokenProvider = $accessTokenProvider;
        $this->httpMessageFactory = $httpMessageFactory;
    }

    protected function processAuthentication(Request $request): ?Response
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);
        $psrResponse = $this->httpMessageFactory->createResponse(new Response(''));

        try {
            $identity = $this->extractTokenIdentity();

            if ($request->isMethod('get')) {
                $authRequest = $this->authorizationServer->validateAuthorizationRequest($psrRequest);

                if (!$this->hasValidAuthorization($authRequest->getClient(), $identity)) {
                    return $this->authorizationApproval->buildAuthorizationView($authRequest, $identity, $request);
                }

                return $this->completeAuthorization(
                    $this->authorizationApproval->approved($authRequest, $identity),
                    $psrResponse
                );
            }

            if ($request->isMethod('post')) {
                return $this->completeAuthorization(
                    $this->authorizationApproval->confirmed($request, $identity),
                    $psrResponse
                );
            }

            if ($request->isMethod('delete')) {
                return $this->authorizationApproval->denied($request);
            }

            throw new AuthenticationException("invalid request");
        } catch (OAuthServerException $exception) {
            return $this->convertResponse($exception->generateHttpResponse($psrResponse));
        }
    }

    protected function completeAuthorization(AuthorizationRequest $authRequest, ResponseInterface $response): Response
    {
        return $this->convertResponse(
            $this->authorizationServer->completeAuthorizationRequest($authRequest, $response)
        );
    }

    protected function hasValidAuthorization(ClientEntityInterface $client, Identity $identity): bool
    {
        $clientModel = $this->clientProvider->clientOfIdentifier($client->getIdentifier());

        // fixMe check scopes from AuthRequest and token
        // todo skip authorization from client

        if ($token = $this->accessTokenProvider->findValidToken($clientModel, $identity)) {
            return true;
        }

        return false;
    }

    public function extractTokenIdentity(): Identity
    {
        if (!$token = $this->guard->storage()->getToken()) {
            throw new AuthenticationException("login");
        }

        if (!$token instanceof LocalToken) {
            throw new AuthenticationException("local token only");
        }

        return $token->getIdentity();
    }

    protected function requireAuthentication(Request $request): bool
    {
        return $request->is('oauth/authorize*');
    }
}