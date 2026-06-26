<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class SubmissionUploadTest extends AbstractLspWebTestCase
{
    public function testFullUploadFlowCreatesProjectAndPromotesBlob(): void
    {
        $xml = '<?xml version="1.0"?><lmms-project creatorversion="1.2.2" type="song"/>';
        $srcPath = sys_get_temp_dir() . '/lsp-upload-' . bin2hex(random_bytes(4)) . '.mmpz';
        file_put_contents($srcPath, gzencode($xml));
        $upload = new UploadedFile($srcPath, 'fixture-upload.mmpz', 'application/octet-stream', test: true);

        $this->client->request(
            'POST',
            '/lsp/add',
            ['ok' => 'OK', 'nocopyright' => '1'],
            ['filename' => $upload],
            ['PHP_AUTH_USER' => 'lsptest', 'PHP_AUTH_PW' => 'lsptestpass'],
        );

        self::assertResponseIsSuccessful();
        $body = (string) $this->client->getResponse()->getContent();
        self::assertMatchesRegularExpression(
            '/name="tmpname"\s+value="([^"]+)"/',
            $body,
            'metadata form should expose the draft tmpname',
        );
        preg_match('/name="tmpname"\s+value="([^"]+)"/', $body, $m);
        $tmpname = $m[1];
        self::assertFileExists($tmpname);

        [$pair] = $this->pickCategoryPair();
        $licenseName = $this->pickLicenseName();

        $this->client->request(
            'POST',
            '/lsp/add',
            [
                'addfinalok'  => 'Add File',
                'tmpname'     => $tmpname,
                'fn'          => 'fixture-upload.mmpz',
                'category'    => $pair,
                'license'     => $licenseName,
                'description' => 'e2e test upload',
                'fsize'       => '42',
            ],
            [],
            ['PHP_AUTH_USER' => 'lsptest', 'PHP_AUTH_PW' => 'lsptestpass'],
        );

        self::assertResponseRedirects();
        self::assertMatchesRegularExpression(
            '#^/lsp/\d+$#',
            (string) $this->client->getResponse()->headers->get('Location'),
        );
        $location = (string) $this->client->getResponse()->headers->get('Location');
        $newId = (int) substr($location, strrpos($location, '/') + 1);

        $row = $this->connection->fetchAssociative(
            'SELECT filename, description, size, hash FROM files WHERE id = :id',
            ['id' => $newId],
        );
        self::assertNotFalse($row);
        self::assertSame('fixture-upload.mmpz', $row['filename']);
        self::assertSame('e2e test upload', $row['description']);
        self::assertSame(42, (int) $row['size']);
        self::assertSame(sha1(gzencode($xml)), $row['hash']);

        $finalPath = dirname($tmpname) . '/' . $newId;
        self::assertFileExists($finalPath);
        self::assertFileDoesNotExist($tmpname);

        @unlink($finalPath);
        $this->deleteTestFile($newId);
    }

    public function testUploadRejectsUnsupportedExtension(): void
    {
        $srcPath = sys_get_temp_dir() . '/lsp-upload-bad-' . bin2hex(random_bytes(4)) . '.exe';
        file_put_contents($srcPath, 'malicious');
        $upload = new UploadedFile($srcPath, 'evil.exe', 'application/octet-stream', test: true);

        $this->client->request(
            'POST',
            '/lsp/add',
            ['ok' => 'OK', 'nocopyright' => '1'],
            ['filename' => $upload],
            ['PHP_AUTH_USER' => 'lsptest', 'PHP_AUTH_PW' => 'lsptestpass'],
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testFinalSubmitRejectsTmpnameOutsideDataDir(): void
    {
        $outsidePath = sys_get_temp_dir() . '/lsp-outside-' . bin2hex(random_bytes(4));
        file_put_contents($outsidePath, 'x');

        try {
            [$pair] = $this->pickCategoryPair();

            $this->client->request(
                'POST',
                '/lsp/add',
                [
                    'addfinalok'  => 'Add File',
                    'tmpname'     => $outsidePath,
                    'fn'          => 'evil.mmpz',
                    'category'    => $pair,
                    'license'     => $this->pickLicenseName(),
                    'description' => '',
                    'fsize'       => '1',
                ],
                [],
                ['PHP_AUTH_USER' => 'lsptest', 'PHP_AUTH_PW' => 'lsptestpass'],
            );

            self::assertResponseStatusCodeSame(400);
        } finally {
            @unlink($outsidePath);
        }
    }
}
