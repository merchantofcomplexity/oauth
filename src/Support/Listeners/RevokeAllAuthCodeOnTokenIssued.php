<?php

namespace MerchantOfComplexity\Oauth\Support\Listeners;

use League\Event\EventInterface;
use League\Event\ListenerInterface;
use League\OAuth2\Server\RequestEvent;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;

class RevokeAllAuthCodeOnTokenIssued implements ListenerInterface
{
    /**
     * @var $clientProvider
     */
    private $clientProvider;

    public function __construct(ProvideClient $clientProvider)
    {
        $this->clientProvider = $clientProvider;
    }

    public function handle(EventInterface $event): void
    {
        if (!$event instanceof RequestEvent || $event->getName() !== RequestEvent::ACCESS_TOKEN_ISSUED) {
            return;
        }

        $this->clientProvider->revokeAuthCodesByClientId(
            $event->getRequest()->getParsedBody()['client_id']
        );
    }

    public function isListener($listener): bool
    {
        return $listener === $this;
    }
}