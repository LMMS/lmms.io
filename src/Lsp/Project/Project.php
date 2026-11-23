<?php

declare(strict_types=1);

namespace App\Lsp\Project;

use App\Security\User;

final readonly class Project
{
    public function __construct(
        public int $id,
        public int $userId,
        public string $filename,
        public ?string $description,
        public string $category,
        public string $subcategory,
        public string $license,
    ) {}

    public static function fromRow(array $row): self
    {
        return new self(
            id:          (int) $row['id'],
            userId:      (int) $row['user_id'],
            filename:    (string) $row['filename'],
            description: $row['description'] !== null ? (string) $row['description'] : null,
            category:    (string) $row['category'],
            subcategory: (string) $row['subcategory'],
            license:     (string) $row['license'],
        );
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->userId === $user->getId();
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->isOwnedBy($user) || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    public function canBeRatedBy(User $user): bool
    {
        return !$this->isOwnedBy($user);
    }
}
