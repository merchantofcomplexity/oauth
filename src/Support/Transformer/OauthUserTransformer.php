<?php

namespace MerchantOfComplexity\Oauth\Support\Transformer;

use League\OAuth2\Server\Entities\UserEntityInterface;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Oauth\League\Entity\Identity as IdentityEntity;
use MerchantOfComplexity\Oauth\Support\Contracts\Transformer\OauthUserTransformer as Transformer;

class OauthUserTransformer implements Transformer
{
    public function __invoke(?Identity $identity): UserEntityInterface
    {
        $identityEntity = new IdentityEntity();

        if($identity){
            $identityEntity->setIdentifier($identity->getIdentifier()->identify());
        }

        return $identityEntity;
    }
}