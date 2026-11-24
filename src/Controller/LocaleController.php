<?php

declare(strict_types=1);

namespace App\Controller;

use App\Locale\AvailableLocales;
use App\Locale\LocaleListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

final class LocaleController extends AbstractController
{
    public function switch(Request $request, AvailableLocales $locales): RedirectResponse
    {
        $response = new RedirectResponse($this->safeReturnPath($request->request->getString('return')));

        $locale = $request->request->getString('locale');
        if ($locales->has($locale) && $this->isCsrfTokenValid('locale', $request->request->getString('_csrf_token'))) {
            $response->headers->setCookie(Cookie::create(LocaleListener::COOKIE_NAME, $locale, '+1 year'));
        }

        return $response;
    }

    private function safeReturnPath(string $path): string
    {
        // browsers read "\" as "/" and drop tabs and newlines, so "/\x" and "/\t/x" both become "//x"
        if (!str_starts_with($path, '/') || str_starts_with($path, '//') || preg_match('/[\\\\\x00-\x1F\x7F]/', $path)) {
            return '/';
        }

        return $path;
    }
}
