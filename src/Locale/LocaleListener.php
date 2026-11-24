<?php

declare(strict_types=1);

namespace App\Locale;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

// after the router (32), before Symfony's LocaleListener (16) reads _locale
#[AsEventListener(priority: 20)]
// after Symfony's ResponseListener (0) fills in the default Content-Type
#[AsEventListener(method: 'addVary', priority: -10)]
final class LocaleListener
{
    public const COOKIE_NAME = 'locale';

    public function __construct(private readonly AvailableLocales $locales) {}

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->attributes->has('_locale')) {
            return;
        }

        $locale = $request->cookies->get(self::COOKIE_NAME);
        if (!is_string($locale) || !$this->locales->has($locale)) {
            $locale = $this->locales->negotiate($request);
        }

        $request->attributes->set('_locale', $locale);
    }

    /**
     * Only HTML pages are translated. Shared caches won't store anything that varies on Cookie.
     */
    public function addVary(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ($event->isMainRequest() && !$response->isRedirect() && str_starts_with((string) $response->headers->get('Content-Type'), 'text/html')) {
            $response->setVary(['Accept-Language', 'Cookie'], false);
        }
    }
}
