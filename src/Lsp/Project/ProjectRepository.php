<?php

declare(strict_types=1);

namespace App\Lsp\Project;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

final class ProjectRepository
{
    private const PAGE_SIZE = 25;

    public function __construct(
        private readonly Connection $connection,
        private readonly ProjectMetadata $projectMetadata,
        private readonly ThumbnailGenerator $thumbnails,
    ) {}

    public function getLatest(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT files.id, licenses.name AS license, size, realname, filename, users.login,
                categories.name AS category, subcategories.name AS subcategory,
                insert_date, update_date, description, files.downloads,
                (SELECT COUNT(file_id) FROM comments WHERE file_id = files.id) AS comments,
                (SELECT COALESCE(AVG(stars), 0) FROM ratings WHERE file_id = files.id) AS rating,
                (SELECT COUNT(id) FROM ratings WHERE file_id = files.id) AS rating_count
            FROM files
            INNER JOIN categories ON categories.id = files.category
            INNER JOIN subcategories ON subcategories.id = files.subcategory
            INNER JOIN users ON users.id = files.user_id
            INNER JOIN licenses ON licenses.id = files.license_id
            ORDER BY files.insert_date DESC
            LIMIT ' . self::PAGE_SIZE,
        );
    }

    public function search(
        string $category = '',
        string $subcategory = '',
        string $search = '',
        string $userName = '',
        string $sort = 'date',
        string $order = 'DESC',
        int $page = 0,
        bool $commentSearch = false,
    ): array {
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $qb = $this->buildBaseQuery();
        $this->applyFilters($qb, $category, $subcategory, $search, $userName, $commentSearch);

        $count = (int) (clone $qb)
            ->select('COUNT(files.id)')
            ->executeQuery()
            ->fetchOne();

        $orderBy = match ($sort) {
            'downloads' => "files.downloads * files.downloads / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(files.insert_date)) $order",
            'rating'    => "rating $order, rating_count $order",
            default     => "files.insert_date $order",
        };

        $rows = $qb
            ->select(
                'files.id, licenses.name AS license, size, realname, filename, users.login,
                categories.name AS category, subcategories.name AS subcategory,
                files.downloads * files.downloads / (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(files.insert_date)) AS downloads_per_day,
                files.downloads, insert_date, update_date, description,
                (SELECT COUNT(file_id) FROM comments WHERE file_id = files.id) AS comments,
                (SELECT COUNT(id) FROM ratings WHERE file_id = files.id) AS rating_count,
                (SELECT COALESCE(AVG(stars), 0) FROM ratings WHERE file_id = files.id) AS rating'
            )
            ->orderBy($orderBy)
            ->setFirstResult($page * self::PAGE_SIZE)
            ->setMaxResults(self::PAGE_SIZE)
            ->executeQuery()
            ->fetchAllAssociative();

        return [$count, $rows];
    }

    public function findById(int $id, ?int $userId = null): ?array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT licenses.name AS license, size, realname, filename, users.login,
                categories.name AS category, subcategories.name AS subcategory,
                insert_date, update_date, description, downloads, files.id,
                (SELECT COUNT(file_id) FROM comments WHERE file_id = files.id) AS comments,
                (SELECT COALESCE(AVG(stars), 0) FROM ratings WHERE file_id = files.id) AS rating,
                (SELECT COUNT(id) FROM ratings WHERE file_id = files.id) AS rating_count
            FROM files
            INNER JOIN categories ON categories.id = files.category
            INNER JOIN subcategories ON subcategories.id = files.subcategory
            INNER JOIN users ON users.id = files.user_id
            INNER JOIN licenses ON licenses.id = files.license_id
            WHERE files.id = :id',
            ['id' => $id],
        );

        if ($row === false) {
            return null;
        }

        $row['comment_section'] = $this->getComments($id);
        $row['session_rating'] = $userId !== null ? $this->getUserRating($id, $userId) : 0;
        $row['lmms_version'] = $this->projectMetadata->creatorVersion($id, $row['filename']);
        $row['thumb'] = $this->thumbnails->generate($id, $row['filename']);

        return $row;
    }

    public function listForWebResources(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT files.filename AS fname, files.hash AS hash,
                categories.name AS catname, subcategories.name AS subcatname,
                files.size AS size, files.update_date AS date,
                users.login AS author
            FROM files
            INNER JOIN categories    ON categories.id    = files.category
            INNER JOIN subcategories ON subcategories.id = files.subcategory
            INNER JOIN users         ON users.id         = files.user_id
            ORDER BY files.id',
        );
    }

    public function findProject(int $id): ?Project
    {
        $row = $this->findForEdit($id);
        return $row !== null ? Project::fromRow($row) : null;
    }

    public function findForEdit(int $id): ?array
    {
        return $this->connection->fetchAssociative(
            'SELECT files.id, files.user_id, filename, description,
                categories.name AS category, subcategories.name AS subcategory,
                licenses.name AS license
            FROM files
            INNER JOIN categories ON categories.id = files.category
            INNER JOIN subcategories ON subcategories.id = files.subcategory
            INNER JOIN licenses ON licenses.id = files.license_id
            WHERE files.id = :id',
            ['id' => $id],
        ) ?: null;
    }

    public function updateProject(
        int $id,
        int $categoryId,
        int $subcategoryId,
        int $licenseId,
        string $description,
    ): void {
        $this->connection->executeStatement(
            'UPDATE files SET update_date = NOW(), category = :category, subcategory = :subcategory,
            license_id = :license, description = :description WHERE id = :id',
            [
                'category'    => $categoryId,
                'subcategory' => $subcategoryId,
                'license'     => $licenseId,
                'description' => $description,
                'id'          => $id,
            ],
        );
    }

    public function insertProject(
        string $filename,
        int $userId,
        int $categoryId,
        int $subcategoryId,
        int $licenseId,
        string $description,
        int $size,
        string $hash,
    ): int {
        $this->connection->executeStatement(
            'INSERT INTO files (filename, user_id, insert_date, update_date, category, subcategory, license_id, description, size, hash)
            VALUES (:filename, :userId, NOW(), NOW(), :category, :subcategory, :license, :description, :size, :hash)',
            [
                'filename'    => $filename,
                'userId'      => $userId,
                'category'    => $categoryId,
                'subcategory' => $subcategoryId,
                'license'     => $licenseId,
                'description' => $description,
                'size'        => $size,
                'hash'        => $hash,
            ],
        );

        return (int) $this->connection->lastInsertId();
    }

    private function getComments(int $fileId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT users.realname, users.login, comments.user_id AS commentuser,
                files.user_id AS fileuser, date, text
            FROM comments
            INNER JOIN users ON users.id = comments.user_id
            INNER JOIN files ON files.id = comments.file_id
            WHERE file_id = :fileId
            ORDER BY date',
            ['fileId' => $fileId],
        );
    }

    private function getUserRating(int $fileId, int $userId): int
    {
        $stars = $this->connection->fetchOne(
            'SELECT stars FROM ratings WHERE file_id = :fileId AND user_id = :userId',
            ['fileId' => $fileId, 'userId' => $userId],
        );

        return $stars !== false ? (int) $stars : 0;
    }

    private function buildBaseQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->from('files')
            ->innerJoin('files', 'categories',    'categories',    'categories.id = files.category')
            ->innerJoin('files', 'subcategories', 'subcategories', 'subcategories.id = files.subcategory')
            ->innerJoin('files', 'users',         'users',         'users.id = files.user_id')
            ->innerJoin('files', 'licenses',      'licenses',      'licenses.id = files.license_id');
    }

    private function applyFilters(
        QueryBuilder $qb,
        string $category,
        string $subcategory,
        string $search,
        string $userName,
        bool $commentSearch,
    ): void {
        if ($category !== '') {
            $qb->andWhere('categories.name = :category')
               ->setParameter('category', $category);
        }

        if ($subcategory !== '') {
            $qb->andWhere('subcategories.name = :subcategory')
               ->setParameter('subcategory', html_entity_decode($subcategory));
        }

        if ($userName !== '') {
            $userId = $this->connection->fetchOne(
                'SELECT id FROM users WHERE LOWER(login) = LOWER(:login)',
                ['login' => $userName],
            );
            $qb->andWhere('files.user_id = :userId')
               ->setParameter('userId', $userId ?: -1);
        }

        if ($search !== '') {
            $like = '%' . $search . '%';
            $condition = $qb->expr()->or(
                'files.filename LIKE :search',
                'users.login LIKE :search',
                'users.realname LIKE :search',
            );

            if ($commentSearch) {
                $matchingFileIds = $this->connection->fetchFirstColumn(
                    'SELECT DISTINCT file_id FROM comments WHERE text LIKE :search',
                    ['search' => $like],
                );
                if ($matchingFileIds !== []) {
                    $condition = $qb->expr()->or(
                        $condition,
                        $qb->expr()->in('files.id', array_map('intval', $matchingFileIds)),
                    );
                }
            }

            $qb->andWhere($condition)->setParameter('search', $like);
        }
    }
}
