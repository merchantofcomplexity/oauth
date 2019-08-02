<?php

namespace MerchantOfComplexity\Oauth\Support;

use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

trait ConvertPsrResponses
{
    public function convertResponse(ResponseInterface $psrResponse): Response
    {
        return new Response(
            $psrResponse->getBody(),
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders()
        );
    }
}