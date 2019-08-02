<?php

namespace MerchantOfComplexity\Oauth\Http\Response;

use Illuminate\Http\Request;
use MerchantOfComplexity\Authters\Support\Contract\Application\Http\Response\Entrypoint;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

final class OauthEntrypoint implements Entrypoint
{
    public function startAuthentication(Request $request, AuthenticationException $unauthorized = null): Response
    {
        $unauthorized = new UnauthorizedHttpException('Bearer');

        return new Response('', $unauthorized->getStatusCode(), $unauthorized->getHeaders());
    }
}