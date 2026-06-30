<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

use App\Lsp\Submission\FileTypeCatalog;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FileTypeCatalogTest extends TestCase
{
    #[DataProvider('extensionProvider')]
    public function testExtensionOf(string $filename, string $expected): void
    {
        $catalog = new FileTypeCatalog($this->createMock(Connection::class));

        self::assertSame($expected, $catalog->extensionOf($filename));
    }

    public static function extensionProvider(): array
    {
        return [
            'simple mmp'        => ['song.mmp', '.mmp'],
            'gzipped mmpz'      => ['song.mmpz', '.mmpz'],
            'uppercase'         => ['SONG.MMP', '.mmp'],
            'mixed case'        => ['Song.MmPz', '.mmpz'],
            'png image'         => ['art.png', '.png'],
            'tar gz compound'   => ['bundle.tar.gz', '.tar.gz'],
            'tar bz2 compound'  => ['bundle.tar.bz2', '.tar.bz2'],
            'no extension'      => ['README', '.'],
            'dot at end'        => ['weird.', '.'],
            'multiple dots'     => ['my.song.mmp', '.mmp'],
        ];
    }
}
