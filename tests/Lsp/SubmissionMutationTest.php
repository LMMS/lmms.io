<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

final class SubmissionMutationTest extends AbstractLspWebTestCase
{
    public function testDeleteFileRequiresLogin(): void
    {
        $this->client->request('GET', '/lsp/delete/1');

        self::assertResponseRedirects('/lsp/login');
    }

    public function testDeleteFileNotFound(): void
    {
        $this->client->request('GET', '/lsp/delete/999999999', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteFileForbiddenRedirectsToShow(): void
    {
        $fileId = $this->createTestFile('otheruser');

        $this->client->request('GET', '/lsp/delete/' . $fileId, [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseRedirects('/lsp/' . $fileId);

        $this->deleteTestFile($fileId);
    }

    public function testEditFileRequiresLogin(): void
    {
        $this->client->request('GET', '/lsp/edit/1');

        self::assertResponseRedirects('/lsp/login');
    }

    public function testEditFileNotFound(): void
    {
        $this->client->request('GET', '/lsp/edit/999999999', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditFileForbiddenForOtherUser(): void
    {
        $fileId = $this->createTestFile('otheruser');

        $this->client->request('GET', '/lsp/edit/' . $fileId, [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseStatusCodeSame(403);

        $this->deleteTestFile($fileId);
    }

    public function testAddFileRequiresLogin(): void
    {
        $this->client->request('GET', '/lsp/add');

        self::assertResponseRedirects('/lsp/login');
    }

    public function testAddFileFormShownWhenLoggedIn(): void
    {
        $this->client->request('GET', '/lsp/add', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form input[name="filename"]');
    }

    public function testAddFileRejectsMissingCopyrightAck(): void
    {
        $this->client->request('POST', '/lsp/add', ['ok' => 'OK'], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.alert-danger', 'Copyrighted content is forbidden');
    }

    public function testDownloadNotFound(): void
    {
        $this->client->request('GET', '/lsp/download/999999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testEditUpdatesMetadata(): void
    {
        $fileId = $this->createTestFile();
        [$pair, $categoryId, $subcategoryId] = $this->pickCategoryPair();
        $licenseName = $this->pickLicenseName();
        $licenseId = (int) $this->connection->fetchOne(
            'SELECT id FROM licenses WHERE name = :name',
            ['name' => $licenseName],
        );

        $this->client->request('POST', '/lsp/edit/' . $fileId, [
            'updateok'    => 'OK',
            'category'    => $pair,
            'license'     => $licenseName,
            'description' => 'Updated description',
        ], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseRedirects('/lsp/' . $fileId);

        $row = $this->connection->fetchAssociative(
            'SELECT category, subcategory, license_id, description FROM files WHERE id = :id',
            ['id' => $fileId],
        );
        self::assertSame($categoryId, (int) $row['category']);
        self::assertSame($subcategoryId, (int) $row['subcategory']);
        self::assertSame($licenseId, (int) $row['license_id']);
        self::assertSame('Updated description', $row['description']);

        $this->deleteTestFile($fileId);
    }

    public function testDeleteRemovesOwnedFile(): void
    {
        $fileId = $this->createTestFile();

        $this->client->request('POST', '/lsp/delete/' . $fileId, [
            'confirmation' => 'true',
        ], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseIsSuccessful();

        $count = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM files WHERE id = :id',
            ['id' => $fileId],
        );
        self::assertSame(0, $count);
    }

    public function testDeleteAlsoRemovesCommentsAndRatings(): void
    {
        $fileId = $this->createTestFile();
        $userId = (int) $this->connection->fetchOne(
            'SELECT id FROM users WHERE login = :login',
            ['login' => 'lsptest'],
        );
        $this->connection->executeStatement(
            'INSERT INTO comments (user_id, file_id, text, date) VALUES (:uid, :fid, :text, NOW())',
            ['uid' => $userId, 'fid' => $fileId, 'text' => 'hi'],
        );
        $this->connection->executeStatement(
            'INSERT INTO ratings (file_id, user_id, stars) VALUES (:fid, :uid, 3)',
            ['fid' => $fileId, 'uid' => $userId],
        );

        $this->client->request('POST', '/lsp/delete/' . $fileId, [
            'confirmation' => 'true',
        ], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertSame(0, (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM comments WHERE file_id = :id',
            ['id' => $fileId],
        ));
        self::assertSame(0, (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ratings WHERE file_id = :id',
            ['id' => $fileId],
        ));
    }
}
