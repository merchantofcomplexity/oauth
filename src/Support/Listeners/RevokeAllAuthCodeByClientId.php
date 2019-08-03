<?php

namespace MerchantOfComplexity\Oauth\Support\Listeners;

use League\Event\EventInterface;
use League\Event\ListenerInterface;
use League\OAuth2\Server\RequestEvent;
use MerchantOfComplexity\Oauth\Infrastructure\Client\ClientProvider;
use Psr\Http\Message\ServerRequestInterface;

class RevokeAllAuthCodeByClientId implements ListenerInterface
{
    /**
     * @var $clientProvider
     */
    private $clientProvider;

    public function __construct(ClientProvider $clientProvider)
    {
        $this->clientProvider = $clientProvider;
    }

    public function handle(EventInterface $event): void
    {
        if ($event->getName() !== RequestEvent::ACCESS_TOKEN_ISSUED) {
            return;
        }

        /** @var ServerRequestInterface $request */
        $request = $event->getRequest();

        $this->clientProvider->revokeAllAuthCode(
            $request->getParsedBody()['client_id']
        );
    }

    public function isListener($listener): bool
    {
        return $listener === $this;
    }
}