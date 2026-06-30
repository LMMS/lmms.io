<?php

declare(strict_types=1);

namespace App\Tests\Lsp;

final class SubmissionCommentsTest extends AbstractLspWebTestCase
{
    private int $testFileId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFileId = $this->createTestFile();
    }

    protected function tearDown(): void
    {
        $this->connection->executeStatement('DELETE FROM comments WHERE user_id = (SELECT id FROM users WHERE login = :login)', ['login' => 'lsptest']);
        $this->connection->executeStatement('DELETE FROM files WHERE id = :id', ['id' => $this->testFileId]);
        parent::tearDown();
    }

    public function testCommentRequiresLogin(): void
    {
        $this->client->request('GET', '/lsp/comment/1');

        self::assertResponseRedirects('/lsp/login');
    }

    public function testCommentFormShownWhenLoggedIn(): void
    {
        $this->client->request('GET', '/lsp/comment/' . $this->testFileId, [], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form textarea[name="text"]');
    }

    public function testCommentRejectsEmptyText(): void
    {
        $this->client->request('POST', '/lsp/comment/' . $this->testFileId, ['addcomment' => 'Comment', 'text' => ''], [], [
            'PHP_AUTH_USER' => 'lsptest',
            'PHP_AUTH_PW'   => 'lsptestpass',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert-danger', 'Please type a message');
    }

    public function testCommentWithHtmlEntitiesIsDecodedBeforeStorage(): void
    {
        $this->client->request('POST', '/lsp/comment/' . $this->testFileId, [
            'addcomment' => 'Comment',
            'text'       => 'AT&amp;T makes &lt;great&gt; music',
        ], [], ['PHP_AUTH_USER' => 'lsptest', 'PHP_AUTH_PW' => 'lsptestpass']);

        self::assertSame('AT&T makes <great> music', $this->connection->fetchOne(
            'SELECT text FROM comments WHERE user_id = (SELECT id FROM users WHERE login = :login) ORDER BY date DESC LIMIT 1',
            ['login' => 'lsptest'],
        ));
    }

    public function testPrePortCommentWithDecodedEntitiesRendersCorrectly(): void
    {
        $this->connection->executeStatement(
            'INSERT INTO comments (user_id, file_id, text, date)
             VALUES ((SELECT id FROM users WHERE login = :login), :fid, :text, NOW())',
            ['login' => 'lsptest', 'fid' => $this->testFileId, 'text' => 'AT&T makes <great> music'],
        );

        $this->client->request('GET', '/lsp/' . $this->testFileId);

        self::assertResponseIsSuccessful();
        self::assertStringContainsString(
            'AT&amp;T makes &lt;great&gt; music',
            (string)$this->client->getResponse()->getContent(),
        );
    }
}
