<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

use App\Lsp\Submission\DraftStorage;
use App\Lsp\Submission\FileTypeCatalog;
use App\Lsp\Submission\InvalidDraftSubmissionException;
use App\Lsp\Submission\UnsupportedFileTypeException;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DraftStorageTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/lsp-draft-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $f) {
            unlink($f);
        }
        rmdir($this->dir);
    }

    private function catalog(array $permittedExtensions): FileTypeCatalog
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAllAssociative')->willReturnCallback(
            static fn (string $sql, array $params) =>
                in_array(strtolower($params['ext']), array_map('strtolower', $permittedExtensions), true)
                    ? [['value' => 'Projects - Songs']]
                    : []
        );
        $connection->method('fetchFirstColumn')->willReturn($permittedExtensions);

        return new FileTypeCatalog($connection);
    }

    private function uploadedFile(string $name, string $contents): UploadedFile
    {
        $src = sys_get_temp_dir() . '/lsp-draft-src-' . bin2hex(random_bytes(4));
        file_put_contents($src, $contents);

        return new UploadedFile($src, $name, null, null, true);
    }

    public function testOpenStoresFileUnderRandomNameInDataDir(): void
    {
        $storage = new DraftStorage($this->catalog(['.mmpz']), $this->dir);
        $upload = $this->uploadedFile('song.mmpz', 'fake-mmpz');

        $path = $storage->open($upload);

        self::assertSame(realpath($this->dir), realpath(dirname($path)));
        self::assertFileExists($path);
        self::assertSame('fake-mmpz', file_get_contents($path));
        self::assertMatchesRegularExpression('/^[a-f0-9]{32}$/', basename($path));
    }

    public function testOpenRejectsUnsupportedExtension(): void
    {
        $storage = new DraftStorage($this->catalog(['.mmpz']), $this->dir);
        $upload = $this->uploadedFile('virus.exe', 'nope');

        $this->expectException(UnsupportedFileTypeException::class);
        $storage->open($upload);
    }

    public function testAssertResumableAcceptsFileInDataDir(): void
    {
        $storage = new DraftStorage($this->catalog([]), $this->dir);
        $path = $this->dir . '/legit';
        file_put_contents($path, 'x');

        $storage->assertResumable($path);
        $this->expectNotToPerformAssertions();
    }

    public function testAssertResumableRejectsPathTraversal(): void
    {
        $storage = new DraftStorage($this->catalog([]), $this->dir);

        $this->expectException(InvalidDraftSubmissionException::class);
        $storage->assertResumable($this->dir . '/../etc/passwd');
    }

    public function testAssertResumableRejectsFileOutsideDataDir(): void
    {
        $storage = new DraftStorage($this->catalog([]), $this->dir);
        $outside = sys_get_temp_dir() . '/lsp-outside-' . bin2hex(random_bytes(4));
        file_put_contents($outside, 'x');

        try {
            $this->expectException(InvalidDraftSubmissionException::class);
            $storage->assertResumable($outside);
        } finally {
            @unlink($outside);
        }
    }

    public function testAssertResumableRejectsMissingFile(): void
    {
        $storage = new DraftStorage($this->catalog([]), $this->dir);

        $this->expectException(InvalidDraftSubmissionException::class);
        $storage->assertResumable($this->dir . '/does-not-exist');
    }

    public function testHashReturnsSha1OfFile(): void
    {
        $storage = new DraftStorage($this->catalog([]), $this->dir);
        $path = $this->dir . '/blob';
        file_put_contents($path, 'hello');

        self::assertSame(sha1('hello'), $storage->hash($path));
    }

    public function testPromoteToRenamesFile(): void
    {
        $storage = new DraftStorage($this->catalog([]), $this->dir);
        $src = $this->dir . '/draft';
        $dst = $this->dir . '/42';
        file_put_contents($src, 'payload');

        $storage->promoteTo($src, $dst);

        self::assertFileDoesNotExist($src);
        self::assertSame('payload', file_get_contents($dst));
    }
}
