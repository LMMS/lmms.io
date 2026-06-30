<?php

declare(strict_types=1);

namespace App\Lsp\Submission;

use Symfony\Component\HttpFoundation\Request;

final readonly class SubmissionMetadata
{
    public function __construct(
        public string $filename,
        public string $categorySubcategory,
        public string $licenseName,
        public string $description,
        public int $size,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            filename:            html_entity_decode($request->request->getString('fn')),
            categorySubcategory: $request->request->getString('category'),
            licenseName:         $request->request->getString('license'),
            description:         $request->request->getString('description'),
            size:                $request->request->getInt('fsize'),
        );
    }
}
