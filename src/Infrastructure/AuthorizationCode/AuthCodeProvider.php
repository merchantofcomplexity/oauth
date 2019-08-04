<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode;

use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAuthCode;

class AuthCodeProvider implements ProvideAuthCode
{
    /**
     * @var AuthCodeModel
     */
    private $model;

    public function __construct(AuthCodeModel $authCodeModel)
    {
        $this->model = $authCodeModel;
    }

    public function authCodeOfIdentifier(string $identifier): ?AuthCodeModel
    {
        return $this->model
            ->newModelQuery()
            ->where('identifier', $identifier)
            ->first();
    }

    public function store(array $data): AuthCodeModel
    {
        $token = $this->model->newInstance($data);

        $token->save($data);

        return $token;
    }
}