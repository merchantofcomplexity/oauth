<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\RefreshToken;

class RefreshTokenProvider
{
    /**
     * @var RefreshTokenModel
     */
    private $model;

    public function __construct(RefreshTokenModel $refreshTokenModel)
    {
        $this->model = $refreshTokenModel;
    }

    public function refreshTokenOfIdentifier(string $identifier): ?RefreshTokenModel
    {
        return $this->model
            ->newModelQuery()
            ->where('identifier', $identifier)
            ->first();
    }

    public function store(array $data): RefreshTokenModel
    {
        $token = $this->model->newInstance($data);

        $token->save($data);

        return $token;
    }
}