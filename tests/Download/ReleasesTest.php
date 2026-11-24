<?php

declare(strict_types=1);

namespace App\Tests\Download;

use App\Download\Releases;
use App\Tests\GitHub\FakeGitHub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class ReleasesTest extends TestCase
{
    public function testDoesNotCallGitHubUntilAsked(): void
    {
        $github = new FakeGitHub(['GET /repos/LMMS/lmms/releases' => self::releases()]);

        new Releases($github->client(), new ArrayAdapter(), 'LMMS', 'lmms');

        self::assertSame([], $github->requests);
    }

    public function testStableAndUnstableShareOneRequest(): void
    {
        $github = new FakeGitHub(['GET /repos/LMMS/lmms/releases' => self::releases()]);
        $releases = new Releases($github->client(), new ArrayAdapter(), 'LMMS', 'lmms');

        self::assertSame('v1.2.2', $releases->latestStableAssets()[0]->getGitRef());
        self::assertSame('v1.3.0-alpha.2', $releases->latestUnstableAssets()[0]->getGitRef());

        self::assertSame(['GET /repos/LMMS/lmms/releases'], $github->requests);
    }

    private static function releases(): array
    {
        $release = fn(string $tag, bool $prerelease) => [
            'name'       => $tag,
            'tag_name'   => $tag,
            'body'       => 'Notes',
            'draft'      => false,
            'prerelease' => $prerelease,
            'assets'     => [[
                'name'                 => "lmms-$tag-win64.exe",
                'browser_download_url' => "https://github.com/LMMS/lmms/releases/download/$tag/lmms-$tag-win64.exe",
                'created_at'           => '2026-09-06T12:00:00Z',
            ]],
        ];

        return [$release('v1.2.1', false), $release('v1.3.0-alpha.2', true), $release('v1.2.2', false)];
    }
}
