<?php
namespace App;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, $defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
        $this->logger = $logger;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $header_locale = $request->getLanguages();
        // get the default locale from the request header
        if (count($header_locale) > 0) {
            $request->setLocale($header_locale[0]);
            $request->attributes->set('_locale', $header_locale[0]);
        }

        if (!$request->hasPreviousSession()) {
            return;
        }

        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
