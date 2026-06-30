<?php

declare(strict_types=1);

namespace App\Lsp\Submission;

use App\Lsp\CategoryRepository;
use App\Lsp\Project\ProjectRepository;

final class ProjectSubmission
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly CategoryRepository $categories,
        private readonly FileTypeCatalog $fileTypes,
        private readonly DraftStorage $drafts,
        private readonly string $lspDataDir,
    ) {}

    /**
     * Resolves the form's category/subcategory/license names to ids, inserts
     * the project row, then renames the draft's tmp file to the new row id
     * under the data dir. The insert + promote pair is coupled — the on-disk
     * name is the freshly minted DB id — and a caller forgetting either
     * leaves the system in an inconsistent state.
     */
    public function accept(string $draftPath, SubmissionMetadata $meta, int $userId): int
    {
        [$categoryId, $subcategoryId] = $this->categories->resolveCombined($meta->categorySubcategory);
        $licenseId = $this->fileTypes->licenseIdByName($meta->licenseName);

        $projectId = $this->projects->insertProject(
            filename:      $meta->filename,
            userId:        $userId,
            categoryId:    $categoryId,
            subcategoryId: $subcategoryId,
            licenseId:     $licenseId,
            description:   $meta->description,
            size:          $meta->size,
            hash:          $this->drafts->hash($draftPath),
        );

        $this->drafts->promoteTo($draftPath, $this->lspDataDir . '/' . $projectId);

        return $projectId;
    }
}
