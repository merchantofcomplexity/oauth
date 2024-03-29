<?php

namespace MerchantOfComplexity\Oauth\Firewall;

use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\ResourceServer;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Contract\Domain\IdentityProvider;
use MerchantOfComplexity\Authters\Support\Contract\Firewall\Key\ContextKey;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\AuthenticationProvider;
use MerchantOfComplexity\Authters\Support\Contract\Guard\Authentication\Tokenable;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationServiceFailure;
use MerchantOfComplexity\Oauth\Support\Contracts\Firewall\OauthToken as BaseOauthToken;
use Psr\Http\Message\ServerRequestInterface;

abstract class ProvideOauthAuthentication implements AuthenticationProvider
{
    /**
     * @var IdentityProvider
     */
    protected $identityProvider;

    /**
     * @var ResourceServer
     */
    private $resourceServer;

    /**
     * @var ContextKey
     */
    private $contextKey;

    public function __construct(IdentityProvider $identityProvider,
                                ResourceServer $resourceServer,
                                ContextKey $contextKey)
    {
        $this->identityProvider = $identityProvider;
        $this->resourceServer = $resourceServer;
        $this->contextKey = $contextKey;
    }

    public function authenticate(Tokenable $token): Tokenable
    {
        if (!$token instanceof BaseOauthToken) {
            throw AuthenticationServiceFailure::unsupportedToken($token);
        }

        $request = $this->validateRequest($token);

        // checkMe oauth user id can be null
        // access token can ne self sufficient and not bound to a user
        $oauthUserId = $request->getAttribute('oauth_user_id');

        $identity = ('' === $oauthUserId) ? null : $this->retrieveIdentity($oauthUserId);

        $token = new OauthToken($request, $this->contextKey, $identity);

        $token->setAuthenticated(true);

        return $token;
    }

    /**
     * @param string $oauthUserId
     * @return Identity
     */
    abstract protected function retrieveIdentity(string $oauthUserId): Identity;

    protected function validateRequest(BaseOauthToken $token): ServerRequestInterface
    {
        try {
            return $this->resourceServer->validateAuthenticatedRequest(
                $token->getServerRequest()
            );
        } catch (OAuthServerException $exception) {
            throw new AuthenticationException("the resource server rejected the request", 0, $exception);
        }
    }

    public function supportToken(Tokenable $token): bool
    {
        return $token instanceof BaseOauthToken
            && $token->getFirewallKey()->sameValueAs($this->contextKey);
    }
}