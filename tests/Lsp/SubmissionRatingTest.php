<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

final class SubmissionRatingTest extends AbstractLspWebTestCase
{
    public function testRatingRequiresLogin(): void
    {
        $this->client->request('GET', '/lsp/rate/1/5');

        self::assertResponseRedirects('/lsp/login');
    }

    public function testRatingRedirectsToShowPage(): void
    {
        $fileId = $this->createTestFile();

        $this->client->request('GET', '/lsp/rate/' . $fileId . '/5', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseRedirects('/lsp/' . $fileId);

        $this->deleteTestFile($fileId);
    }

    public function testRatingPersistsForNonOwner(): void
    {
        $fileId = $this->createTestFile('otheruser');

        $this->client->request('GET', '/lsp/rate/' . $fileId . '/4', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        $stars = (int) $this->connection->fetchOne(
            'SELECT stars FROM ratings WHERE file_id = :fid
             AND user_id = (SELECT id FROM users WHERE login = :login)',
            ['fid' => $fileId, 'login' => 'lsptest'],
        );
        self::assertSame(4, $stars);

        $this->deleteTestFile($fileId);
    }

    public function testReRatingUpdatesInPlace(): void
    {
        $fileId = $this->createTestFile('otheruser');

        foreach ([2, 5] as $stars) {
            $this->client->request('GET', '/lsp/rate/' . $fileId . '/' . $stars, [], [], [
                'PHP_AUTH_USER' => 'lsptest',
                'PHP_AUTH_PW'   => 'lsptestpass',
            ]);
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT stars FROM ratings WHERE file_id = :fid
             AND user_id = (SELECT id FROM users WHERE login = :login)',
            ['fid' => $fileId, 'login' => 'lsptest'],
        );
        self::assertCount(1, $rows);
        self::assertSame(5, (int) $rows[0]['stars']);

        $this->deleteTestFile($fileId);
    }

    public function testOwnerCannotRateOwnFile(): void
    {
        $fileId = $this->createTestFile('lsptest');

        $this->client->request('GET', '/lsp/rate/' . $fileId . '/5', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        $count = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ratings WHERE file_id = :fid',
            ['fid' => $fileId],
        );
        self::assertSame(0, $count);

        $this->deleteTestFile($fileId);
    }

    public function testInvalidStarValueIsNotPersisted(): void
    {
        $fileId = $this->createTestFile('otheruser');

        $this->client->request('GET', '/lsp/rate/' . $fileId . '/9', [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertSame(0, (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM ratings WHERE file_id = :fid',
            ['fid' => $fileId],
        ));

        $this->deleteTestFile($fileId);
    }
}
