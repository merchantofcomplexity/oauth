<?php

namespace MerchantOfComplexity\Oauth\Support\Value;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;

class DeniedClientRedirectUri
{
    /**
     * @var string
     */
    private $uri;

    protected function __construct(string $uri)
    {
        $this->uri = $uri;
    }

    public static function fromRequests(AuthorizationRequest $authRequest, Request $request): self
    {
        $clientUris = Arr::wrap($authRequest->getClient()->getRedirectUri());

        if (!in_array($uri = $authRequest->getRedirectUri(), $clientUris)) {
            $uri = Arr::first($clientUris);
        }

        $separator = $authRequest->getGrantTypeId() === 'implicit'
            ? '#' : (strstr($uri, '?') ? '&' : '?');

        $query = [
            'error' => 'access_denied',
            'state' => $request->input('state')
        ];

        return new self($uri . $separator . http_build_query($query));
    }

    public function getValue(): string
    {
        return $this->uri;
    }
}