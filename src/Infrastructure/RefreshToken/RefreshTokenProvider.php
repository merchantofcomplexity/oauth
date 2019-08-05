<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\RefreshToken;

use Illuminate\Database\Eloquent\Model;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\RefreshTokenInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideRefreshToken;

class RefreshTokenProvider implements ProvideRefreshToken
{
    /**
     * @var RefreshTokenInterface|Model
     */
    private $model;

    public function __construct(RefreshTokenInterface $refreshTokenModel)
    {
        $this->model = $refreshTokenModel;
    }

    public function refreshTokenOfIdentifier(string $identifier): ?RefreshTokenInterface
    {
        return $this->model
            ->newModelQuery()
            ->where('identifier', $identifier)
            ->first();
    }

    public function store(array $data): void
    {
        $token = $this->model->newInstance($data);

        $token->save($data);
    }
}