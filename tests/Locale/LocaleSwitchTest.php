<?php

declare(strict_types=1);

namespace App\Tests\Locale;

use App\Locale\LocaleListener;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

final class LocaleSwitchTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @return iterable<array{string, string}>
     */
    public static function acceptLanguages(): iterable
    {
        yield ['pl-PL,pl;q=0.9,en;q=0.8', 'pl'];
        yield ['xx', 'en'];
        yield ['hi-IN', 'hi'];
        yield ['zh', 'zh-CN'];
        yield ['zh-SG', 'zh-CN'];
        yield ['zh-Hans-HK', 'zh-CN'];
        yield ['zh-TW', 'zh-TW'];
        yield ['zh-HK', 'zh-TW'];
        yield ['zh-MO', 'zh-TW'];
        yield ['zh-Hant', 'zh-TW'];
        yield ['zh-Hant-HK', 'zh-TW'];
        yield ['fr-CA,zh-HK;q=0.5', 'fr'];
    }

    #[DataProvider('acceptLanguages')]
    public function testLocaleIsNegotiatedFromAcceptLanguage(string $acceptLanguage, string $lang): void
    {
        $this->client->request('GET', '/branding', server: ['HTTP_ACCEPT_LANGUAGE' => $acceptLanguage]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists(sprintf('html[lang="%s"]', $lang));
    }

    public function testPagesVaryByLanguageAndCookie(): void
    {
        $this->client->request('GET', '/branding');

        self::assertEqualsCanonicalizing(['Accept-Language', 'Cookie'], $this->client->getResponse()->getVary());
    }

    public function testCookieOverridesAcceptLanguage(): void
    {
        $this->client->getCookieJar()->set(new Cookie(LocaleListener::COOKIE_NAME, 'en'));
        $this->client->request('GET', '/branding', server: ['HTTP_ACCEPT_LANGUAGE' => 'pl']);

        self::assertSelectorExists('html[lang="en"]');
    }

    public function testUnsupportedCookieIsIgnored(): void
    {
        $this->client->getCookieJar()->set(new Cookie(LocaleListener::COOKIE_NAME, 'xx'));
        $this->client->request('GET', '/branding', server: ['HTTP_ACCEPT_LANGUAGE' => 'de']);

        self::assertSelectorExists('html[lang="de"]');
    }

    public function testSwitcherListsLocalesAndMarksCurrent(): void
    {
        $this->client->request('GET', '/branding?x=1', server: ['HTTP_ACCEPT_LANGUAGE' => 'pl']);

        self::assertSelectorExists('.locale-switcher input[name="return"][value="/branding?x=1"]');
        self::assertSelectorExists('.locale-switcher button[name="locale"][value="en"]');
        self::assertSelectorNotExists('.locale-switcher button[name="locale"][value="hi_IN"]');
        self::assertSelectorTextSame('.locale-switcher button[aria-current="true"]', 'Polski');
        self::assertSelectorCount(1, '.locale-switcher button.active');
    }

    public function testSwitchSetsCookieAndRedirectsBack(): void
    {
        $this->client->request('GET', '/branding?x=1');
        $this->client->submitForm('Deutsch');

        self::assertResponseRedirects('/branding?x=1');
        self::assertBrowserCookieValueSame(LocaleListener::COOKIE_NAME, 'de');
        self::assertSame([], $this->client->getResponse()->getVary());

        $this->client->followRedirect();
        self::assertSelectorExists('html[lang="de"]');
    }

    /**
     * @return iterable<array{string}>
     */
    public static function offsiteReturnPaths(): iterable
    {
        yield [''];
        yield ['https://evil.example/'];
        yield ['//evil.example/'];
        yield ['/\\evil.example/'];
        yield ["/\t/evil.example/"];
        yield ["/\n/evil.example/"];
        yield ["/\r\n/evil.example/"];
    }

    #[DataProvider('offsiteReturnPaths')]
    public function testSwitchRefusesOffsiteRedirects(string $return): void
    {
        $this->client->request('GET', '/branding');
        $this->client->submitForm('Deutsch', ['return' => $return]);

        self::assertResponseRedirects('/');
    }

    public function testSwitchIgnoresUnsupportedLocale(): void
    {
        $this->client->request('GET', '/branding');
        $this->client->submitForm('Deutsch', ['locale' => 'xx']);

        self::assertResponseRedirects('/branding');
        self::assertBrowserNotHasCookie(LocaleListener::COOKIE_NAME);
    }

    public function testSwitchIgnoresCrossSiteRequests(): void
    {
        $this->client->request('GET', '/branding');
        $this->client->submitForm('Deutsch', serverParameters: ['HTTP_SEC_FETCH_SITE' => 'cross-site']);

        self::assertResponseRedirects('/branding');
        self::assertBrowserNotHasCookie(LocaleListener::COOKIE_NAME);
    }
}
