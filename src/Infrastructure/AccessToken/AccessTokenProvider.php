<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\AccessToken;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use MerchantOfComplexity\Authters\Exception\RuntimeException;
use MerchantOfComplexity\Authters\Support\Contract\Domain\Identity;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AccessTokenInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\ClientInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithAccessToken;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\Eloquent\WithClient;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAccessToken;

class AccessTokenProvider implements ProvideAccessToken
{
    /**
     * @var WithAccessToken|Model
     */
    private $model;

    public function __construct(WithAccessToken $model)
    {
        $this->model = $model;
    }

    public function tokenOfIdentifier(string $identifier): ?AccessTokenInterface
    {
        return $this->model
            ->newModelQuery()
            ->where('identifier', $identifier)
            ->first();
    }

    public function findValidToken(ClientInterface $clientModel, Identity $identity): ?AccessTokenInterface
    {
        if (!$clientModel instanceof WithClient) {
            throw new RuntimeException("invalid client model");
        }

        $token = $clientModel->tokens()
            ->where('identity_id', $identity->getIdentifier()->identify())
            ->where('revoked', 0)
            ->where('expires_at', '>', Carbon::now())
            ->latest('expires_at')
            ->first();

        return $token instanceof AccessTokenModel ? $token : null;
    }

    public function store(array $data): void
    {
        $token = $this->model->newInstance($data);

        $token->save($data);
    }
}