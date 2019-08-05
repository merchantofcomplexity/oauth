<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Http\Request;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ScopeInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

final class OauthAuthorization extends OauthApproval
{
    protected function authorizeRequest(Identity $identity,
                                        Request $request,
                                        ServerRequestInterface $psrRequest,
                                        ResponseInterface $psrResponse): Response
    {
        $authRequest = $this->authorizationServer->validateAuthorizationRequest($psrRequest);

        if (!$this->hasValidAuthorization($authRequest, $identity)) {
            $scopesModels = $this->scopeManager->toModelArray(...$authRequest->getScopes());

            $scopes = $this->scopeManager->filterScopes(...$scopesModels);

            return $this->buildAuthorizationView($authRequest, $identity, $request, ...$scopes);
        }

        return $this->approveAuthorizationRequest($authRequest, $identity, $psrResponse);
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

        $scopes = $this->scopeManager->filterScopes(...$token->getScopes());

        $scopeEntities = $this->scopeManager->toLeagueArray(...$scopes);

        return $this->scopeManager->equalsScopes($scopeEntities, $authRequest->getScopes());
    }

    protected function buildAuthorizationView(AuthorizationRequest $authorizationRequest,
                                              Identity $identity,
                                              Request $request,
                                              ScopeInterface ...$scopes): Response
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

    protected function requireAuthentication(Request $request): bool
    {
        return $request->is('oauth/authorize*');
    }
}