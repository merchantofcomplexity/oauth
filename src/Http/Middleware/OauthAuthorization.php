<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\LocalToken;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\RecallerToken;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

final class OauthAuthorization extends OauthApproval
{
    protected function processAuthentication(Request $request): ?Response
    {
        $psrRequest = $this->httpMessageFactory->createRequest($request);

        $psrResponse = $this->httpMessageFactory->createResponse(new Response(''));

        try {
            $identity = $this->extractTokenIdentity();

            if ($request->isMethod('get')) {
                $authRequest = $this->authorizationServer->validateAuthorizationRequest($psrRequest);

                if (!$this->hasValidAuthorization($authRequest, $identity)) {
                    $scopesModels = $this->scopeBuilder->toModelArray(...$authRequest->getScopes());

                    $scopes = $this->scopeBuilder->filterScopes(...$scopesModels);

                    return $this->buildAuthorizationView($authRequest, $identity, $request, ...$scopes);
                }

                return $this->completeAuthorization($this->approveRequest($authRequest, $identity), $psrResponse);
            }

            if ($request->isMethod('post')) {
                return $this->completeAuthorization($this->confirmRequest($request, $identity), $psrResponse);
            }

            if ($request->isMethod('delete')) {
                return $this->denyRequest($request);
            }

            throw new AuthenticationException("invalid request");
        } catch (OAuthServerException $exception) {
            return $this->convertResponse($exception->generateHttpResponse($psrResponse));
        }
    }

    protected function hasValidAuthorization(AuthorizationRequest $authRequest, Identity $identity): bool
    {
        $clientModel = $this->clientProvider->clientOfIdentifier(
            $authRequest->getClient()->getIdentifier()
        );

        // todo skip authorization from client

        $token = $this->accessTokenProvider->findValidToken($clientModel, $identity);

        if (!$token) {
            return false;
        }

        $scopes = $this->scopeBuilder->filterScopes(...$token->getScopes());
        $scopeEntities = $this->scopeBuilder->toLeagueArray(...$scopes);

        return $scopeEntities === $authRequest->getScopes();
    }

    public function extractTokenIdentity(): Identity
    {
        if (!$token = $this->guard->storage()->getToken()) {
            throw new AuthenticationException("login");
        }

        if (!$token instanceof LocalToken || !$token instanceof RecallerToken) {
            throw new AuthenticationException("local and recaller token only");
        }

        return $token->getIdentity();
    }

    protected function requireAuthentication(Request $request): bool
    {
        return $request->is('oauth/authorize*');
    }
}