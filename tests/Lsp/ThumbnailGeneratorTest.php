<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

use App\Lsp\Project\ThumbnailGenerator;
use PHPUnit\Framework\TestCase;

final class ThumbnailGeneratorTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/lsp-thumb-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        rmdir($this->dir);
    }

    private function writePng(int $id, int $width, int $height): void
    {
        $im = imagecreatetruecolor($width, $height);
        imagefill($im, 0, 0, imagecolorallocate($im, 200, 100, 50));
        imagepng($im, $this->dir . '/' . $id);
    }

    public function testGeneratesDataUriForLargePng(): void
    {
        $this->writePng(1, 600, 400);

        $thumb = (new ThumbnailGenerator($this->dir))->generate(1, 'art.png');

        self::assertNotNull($thumb);
        self::assertStringStartsWith('data:image/png;base64,', $thumb);

        $decoded = base64_decode(substr($thumb, strlen('data:image/png;base64,')), true);
        self::assertNotFalse($decoded);

        $im = imagecreatefromstring($decoded);
        self::assertNotFalse($im);
        self::assertSame(300, imagesx($im));
        self::assertSame(200, imagesy($im));
    }

    public function testReturnsNullForNonImageExtension(): void
    {
        file_put_contents($this->dir . '/2', 'fake');

        self::assertNull((new ThumbnailGenerator($this->dir))->generate(2, 'song.wav'));
    }

    public function testReturnsNullWhenFileMissing(): void
    {
        self::assertNull((new ThumbnailGenerator($this->dir))->generate(999, 'art.png'));
    }

    public function testReturnsNullWhenImageNarrowerThanThumbWidth(): void
    {
        $this->writePng(3, 100, 100);

        self::assertNull((new ThumbnailGenerator($this->dir))->generate(3, 'art.png'));
    }

    public function testReturnsNullOnUndecodableImage(): void
    {
        file_put_contents($this->dir . '/4', 'not a png');

        self::assertNull((new ThumbnailGenerator($this->dir))->generate(4, 'art.png'));
    }
}
