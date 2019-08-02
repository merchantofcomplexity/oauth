<?php

namespace MerchantOfComplexity\Oauth\League\Entity;

use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

final class Identity implements UserEntityInterface
{
    use EntityTrait;
}