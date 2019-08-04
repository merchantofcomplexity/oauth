<?php

namespace MerchantOfComplexity\Oauth\Firewall;

use MerchantOfComplexity\Authters\Guard\Authentication\Token\Token;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Authters\Support\Contract\Firewall\Key\ContextKey;
use MerchantOfComplexity\Authters\Support\Contract\Firewall\Key\FirewallKey;
use MerchantOfComplexity\Authters\Support\Contract\Value\Credentials;
use MerchantOfComplexity\Oauth\Support\Value\AccessTokenId;
use Psr\Http\Message\ServerRequestInterface;

final class OauthToken extends Token
{
    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var ContextKey
     */
    private $contextKey;

    public function __construct(ServerRequestInterface $serverRequest, ContextKey $contextKey, ?Identity $identity)
    {
        $this->serverRequest = $serverRequest;
        $this->contextKey = $contextKey;

        $roles = $this->buildRolesFromScopes();

        if ($identity) {
            $this->setIdentity($identity);

            $roles = array_merge($roles, $identity->getRoles());
        }

        parent::__construct(array_unique($roles));
    }

    public function getCredentials(): Credentials
    {
        return AccessTokenId::fromString($this->serverRequest->getAttribute('oauth_access_token_id'));
    }

    public function getFirewallKey(): FirewallKey
    {
        return $this->contextKey;
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    protected function buildRolesFromScopes(): array
    {
        $roles = [];

        foreach ($this->serverRequest->getAttribute('oauth_scopes', []) as $scope) {
            $roles[] = sprintf('ROLE_OAUTH_%s', trim(strtoupper($scope)));
        }

        return $roles;
    }
}