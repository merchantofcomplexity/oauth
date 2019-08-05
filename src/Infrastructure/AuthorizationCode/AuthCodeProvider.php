<?php

namespace MerchantOfComplexity\Oauth\Infrastructure\AuthorizationCode;

use Illuminate\Database\Eloquent\Model;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Model\AuthorizationCodeInterface;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideAuthCode;

class AuthCodeProvider implements ProvideAuthCode
{
    /**
     * @var AuthCodeModel|Model
     */
    private $model;

    public function __construct(AuthorizationCodeInterface $authCodeModel)
    {
        $this->model = $authCodeModel;
    }

    public function authCodeOfIdentifier(string $identifier): ?AuthorizationCodeInterface
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