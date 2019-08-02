<?php

namespace MerchantOfComplexity\Oauth\Support;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use MerchantOfComplexity\Oauth\League\Entity\Identity as IdentityEntity;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\ScopeTransformer;
use MerchantOfComplexity\Oauth\Support\Value\ClientRedirectUri;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationApproval
{
    /**
     * @var string
     */
    protected $sessionKey = 'auth_request';

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var OauthUserTransformer
     */
    private $userTransformer;

    /**
     * @var ScopeTransformer
     */
    private $scopeTransformer;

    public function __construct(ResponseFactory $responseFactory,
                                OauthUserTransformer $userTransformer,
                                ScopeTransformer $scopeTransformer)
    {
        $this->responseFactory = $responseFactory;
        $this->userTransformer = $userTransformer;
        $this->scopeTransformer = $scopeTransformer;
    }

    public function approved(AuthorizationRequest $authRequest, Identity $identity): AuthorizationRequest
    {
        $this->approveAuthorizationRequest($authRequest, $identity);

        return $authRequest;
    }

    public function confirmed(Request $request, Identity $identity): AuthorizationRequest
    {
        $authRequest = $this->requireAuthorizationRequestFromSession($request);

        $this->approveAuthorizationRequest($authRequest, $identity);

        return $authRequest;
    }

    public function denied(Request $request): Response
    {
        $authRequest = $this->requireAuthorizationRequestFromSession($request);

        return $this->responseFactory->redirectTo(
            ClientRedirectUri::fromAuthorizationRequest($authRequest, $request)->getValue()
        );
    }

    public function buildAuthorizationView(AuthorizationRequest $authorizationRequest,
                                           Identity $identity,
                                           Request $request): Response
    {
        $request->session()->flash($this->sessionKey, $authorizationRequest);

        // fixMe
        return $this->responseFactory->view('oauth.authorize', [
            'client' => $authorizationRequest->getClient(),
            'request' => $request,
            'identity' => $identity,
            'scopes' => ['todo']
        ]);
    }

    protected function approveAuthorizationRequest(AuthorizationRequest $authRequest, Identity $identity): void
    {
        $identityEntity = new IdentityEntity();

        $identityEntity->setIdentifier(
            $identity->getIdentifier()->identify()
        );

        $authRequest->setUser($identityEntity);

        $authRequest->setAuthorizationApproved(true);
    }

    protected function requireAuthorizationRequestFromSession(Request $request): AuthorizationRequest
    {
        $authRequest = $request->session()->get($this->sessionKey);

        if (!$authRequest instanceof AuthorizationRequest) {
            throw new AuthenticationException("Oauth authorization request failed");
        }

        return $authRequest;
    }
}