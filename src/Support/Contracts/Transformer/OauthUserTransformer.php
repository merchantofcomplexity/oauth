<?php

namespace MerchantOfComplexity\Oauth\Support\Contracts\Transformer;

use League\OAuth2\Server\Entities\UserEntityInterface;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;

interface OauthUserTransformer
{
    /**
     * @param Identity|null $identity
     * @return UserEntityInterface
     */
    public function __invoke(?Identity $identity): UserEntityInterface;
}