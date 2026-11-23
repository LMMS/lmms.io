<?php

declare(strict_types=1);

namespace App\Lsp\Project;

use Symfony\Component\HttpFoundation\Request;

final readonly class EditMetadata
{
    public function __construct(
        public string $categorySubcategory,
        public string $licenseName,
        public string $description,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            categorySubcategory: $request->request->getString('category'),
            licenseName:         $request->request->getString('license'),
            description:         $request->request->getString('description'),
        );
    }
}
