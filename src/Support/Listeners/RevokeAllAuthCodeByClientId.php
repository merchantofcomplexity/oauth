<?php

namespace MerchantOfComplexity\Oauth\Support\Listeners;

use League\Event\EventInterface;
use League\Event\ListenerInterface;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;
use League\OAuth2\Server\RequestEvent;

class RevokeAllAuthCodeByClientId implements ListenerInterface
{
    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function handle(EventInterface $event): void
    {
        if ($event->getName() !== RequestEvent::ACCESS_TOKEN_ISSUED) {
            return;
        }
    }

    public function isListener($listener): bool
    {
        return $listener === $this;
    }
}