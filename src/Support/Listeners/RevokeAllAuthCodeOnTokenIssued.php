<?php

namespace MerchantOfComplexity\Oauth\Support\Listeners;

use League\Event\EventInterface;
use League\Event\ListenerInterface;
use League\OAuth2\Server\RequestEvent;
use MerchantOfComplexity\Oauth\Support\Contracts\Infrastructure\Providers\ProvideClient;
use function is_array;

class RevokeAllAuthCodeOnTokenIssued implements ListenerInterface
{
    /**
     * @var ProvideClient $clientProvider
     */
    private $clientProvider;

    public function __construct(ProvideClient $clientProvider)
    {
        $this->clientProvider = $clientProvider;
    }

    public function handle(EventInterface $event): void
    {
        $clientId = $this->extractClientIdFromEvent($event);

        if ($clientId) {
            $this->clientProvider->revokeAuthCodesByClientId($clientId);
        }
    }

    protected function extractClientIdFromEvent(EventInterface $event): ?string
    {
        if (!$event instanceof RequestEvent || $event->getName() !== RequestEvent::ACCESS_TOKEN_ISSUED) {
            return null;
        }

        if (is_array($parsedBody = $event->getRequest()->getParsedBody())) {
            return $parsedBody['client_id'] ?? null;
        }

        return null;
    }

    public function isListener($listener): bool
    {
        return $listener === $this;
    }
}