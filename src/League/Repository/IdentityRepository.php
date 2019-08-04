<?php

namespace MerchantOfComplexity\Oauth\League\Repository;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use MerchantOfComplexity\Authters\Support\Contract\Domain\IdentityProvider;
use MerchantOfComplexity\Authters\Support\Contract\Domain\LocalIdentity;
use MerchantOfComplexity\Authters\Support\Contract\Validator\CredentialsValidator;
use MerchantOfComplexity\Authters\Support\Exception\AuthenticationException;
use MerchantOfComplexity\Authters\Support\Value\Credentials\ClearPassword;
use MerchantOfComplexity\Authters\Support\Value\Identifier\EmailIdentity;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer;

final class IdentityRepository implements UserRepositoryInterface
{
    /**
     * @var IdentityProvider
     */
    private $identityProvider;

    /**
     * @var CredentialsValidator
     */
    private $credentialsValidator;

    /**
     * @var OauthUserTransformer
     */
    private $userTransformer;

    public function __construct(IdentityProvider $identityProvider,
                                CredentialsValidator $credentialsValidator,
                                OauthUserTransformer $userTransformer)
    {
        $this->identityProvider = $identityProvider;
        $this->credentialsValidator = $credentialsValidator;
        $this->userTransformer = $userTransformer;
    }

    public function getUserEntityByUserCredentials($username,
                                                   $password,
                                                   $grantType,
                                                   ClientEntityInterface $clientEntity): ?UserEntityInterface
    {
        if (!$username || !$password) {
            return null;
        }

        try {
            $identifier = EmailIdentity::fromString($username);
            $password = new ClearPassword($password);

            $identity = $this->identityProvider->requireIdentityOfIdentifier($identifier);

            if (!$identity instanceof LocalIdentity) {
                return null;
            }

            if (!($this->credentialsValidator)($identity->getPassword(), $password)) {
                return null;
            }

            return ($this->userTransformer)($identity);
        } catch (AuthenticationException $exception) {
            return null;
        }
    }
}