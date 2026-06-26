<?php

declare(strict_types=1);

namespace App\Lsp\Project;

use App\Lsp\CategoryRepository;
use App\Lsp\Submission\FileTypeCatalog;

final class ProjectEdit
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly CategoryRepository $categories,
        private readonly FileTypeCatalog $fileTypes,
    ) {}

    public function apply(int $id, EditMetadata $meta): void
    {
        [$categoryId, $subcategoryId] = $this->categories->resolveCombined($meta->categorySubcategory);

        $this->projects->updateProject(
            id:            $id,
            categoryId:    $categoryId,
            subcategoryId: $subcategoryId,
            licenseId:     $this->fileTypes->licenseIdByName($meta->licenseName),
            description:   $meta->description,
        );
    }
}
