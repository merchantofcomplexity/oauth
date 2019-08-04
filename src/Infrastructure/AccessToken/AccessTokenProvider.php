<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\AccessToken;

use Carbon\Carbon;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientModel;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAccessToken;

class AccessTokenProvider implements ProvideAccessToken
{
    /**
     * @var AccessTokenModel
     */
    private $model;

    public function __construct(AccessTokenModel $model)
    {
        $this->model = $model;
    }

    public function tokenOfIdentifier(string $identifier): ?AccessTokenModel
    {
        return $this->model
            ->newModelQuery()
            ->where('identifier', $identifier)
            ->first();
    }

    public function findValidToken(ClientModel $clientModel, Identity $identity): ?AccessTokenModel
    {
        if (!$clientModel->exists) {
            return null;
        }

        $token = $clientModel->tokens()
            ->where('identity_id', $identity->getIdentifier()->identify())
            ->where('revoked', 0)
            ->where('expires_at', '>', Carbon::now())
            ->latest('expires_at')
            ->first();

        return $token instanceof AccessTokenModel ? $token : null;
    }

    public function store(array $data): AccessTokenModel
    {
        $token = $this->model->newInstance($data);

        $token->save($data);

        return $token;
    }
}