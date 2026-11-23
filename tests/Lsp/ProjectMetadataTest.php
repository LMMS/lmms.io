<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

use App\Lsp\Project\ProjectMetadata;
use PHPUnit\Framework\TestCase;

final class ProjectMetadataTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/lsp-meta-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        rmdir($this->dir);
    }

    public function testReadsCreatorVersionFromMmp(): void
    {
        $xml = '<?xml version="1.0"?><lmms-project creatorversion="1.2.2" type="song"/>';
        file_put_contents($this->dir . '/42', $xml);

        $service = new ProjectMetadata($this->dir);

        self::assertSame('1.2.2', $service->creatorVersion(42, 'song.mmp'));
    }

    public function testReadsCreatorVersionFromMmpz(): void
    {
        $xml = '<?xml version="1.0"?><lmms-project creatorversion="1.3.0-beta" type="song"/>';
        file_put_contents($this->dir . '/43', gzencode($xml));

        $service = new ProjectMetadata($this->dir);

        self::assertSame('1.3.0-beta', $service->creatorVersion(43, 'song.mmpz'));
    }

    public function testReturnsNullForNonProjectExtension(): void
    {
        file_put_contents($this->dir . '/44', 'whatever');

        $service = new ProjectMetadata($this->dir);

        self::assertNull($service->creatorVersion(44, 'beat.wav'));
    }

    public function testReturnsNullWhenFileMissing(): void
    {
        $service = new ProjectMetadata($this->dir);

        self::assertNull($service->creatorVersion(999, 'song.mmp'));
    }

    public function testReturnsNullOnInvalidXml(): void
    {
        file_put_contents($this->dir . '/45', 'not xml at all');

        $service = new ProjectMetadata($this->dir);

        self::assertNull($service->creatorVersion(45, 'song.mmp'));
    }

    public function testReturnsNullWhenAttributeMissing(): void
    {
        $xml = '<?xml version="1.0"?><lmms-project type="song"/>';
        file_put_contents($this->dir . '/46', $xml);

        $service = new ProjectMetadata($this->dir);

        self::assertNull($service->creatorVersion(46, 'song.mmp'));
    }
}
