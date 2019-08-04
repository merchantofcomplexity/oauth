<?php

namespace MerchantOfComplexity\Oauth\Http\Middleware;

use Illuminate\Http\Request;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Oauth\Support\Value\DeniedClientRedirectUri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

final class OauthAuthorizationDenied extends OauthApproval
{
    protected function authorizeRequest(Identity $identity,
                                        Request $request,
                                        ServerRequestInterface $psrRequest,
                                        ResponseInterface $psrResponse): Response
    {
        $authRequest = $this->extractAuthorizationRequestFromSession($request);

        return $this->responseFactory->redirectTo(
            DeniedClientRedirectUri::fromRequests($authRequest, $request)->getValue()
        );
    }

    protected function requireAuthentication(Request $request): bool
    {
        return $request->is('oauth/authorize*') && $request->isMethod('delete');
    }
}