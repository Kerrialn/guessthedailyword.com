<?php

declare(strict_types=1);

namespace App\Event\Subscriber;

use App\Service\FingerPrintService;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

#[AsEventListener(event: RequestEvent::class, method: 'generateFingerprint', priority: 90)]
readonly class FingerprintSubscriber
{

    public function __construct(
        private FingerPrintService $fingerPrintService,
    )
    {
    }

    public function generateFingerprint(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if (!$session->has('fingerprint')) {
            $fingerprint = $this->fingerPrintService->generate(request: $request);
            $session->set('fingerprint', $fingerprint);
        }
    }

}
