<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Http\Request;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use MerchantOfComplexity\Authters\Application\Http\Middleware\Authentication;
use MerchantOfComplexity\Oauth\Support\ConvertPsrResponses;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class OauthTokenIssuer extends Authentication
{
    use ConvertPsrResponses;

    /**
     * @var AuthorizationServer
     */
    private $server;

    /**
     * @var HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    public function __construct(AuthorizationServer $server, HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->server = $server;
        $this->httpMessageFactory = $httpMessageFactory;
    }

    protected function processAuthentication(Request $request): ?Response
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);

        $psrResponse = $this->httpMessageFactory->createResponse(new Response());

        try {
            $response = $this->server->respondToAccessTokenRequest($psrRequest, $psrResponse);
        } catch (OAuthServerException $serverException) {
            throw $serverException;
            $response = $serverException->generateHttpResponse($psrResponse);
        }

        return $this->convertResponse($response);
    }

    protected function requireAuthentication(Request $request): bool
    {
        return $request->is('oauth/token*');
    }
}