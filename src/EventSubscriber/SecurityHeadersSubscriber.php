<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/** En-têtes de sécurité sur toutes les réponses. */
final class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onResponse'];
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }
        $h = $event->getResponse()->headers;
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('X-Frame-Options', 'SAMEORIGIN');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        if (!$h->has('Content-Security-Policy')) {
            $h->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "img-src 'self' data:",
                "media-src 'self'",
                "script-src 'self'",
                "style-src 'self'",
                "style-src-attr 'unsafe-inline'",
                'frame-src https://www.youtube-nocookie.com https://player.vimeo.com',
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ]));
        }
    }
}
