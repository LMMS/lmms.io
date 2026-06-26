<?php

declare(strict_types=1);

namespace App\Lsp\Submission;

final class UnsupportedFileTypeException extends \RuntimeException
{
    /**
     * @param list<string> $allowedExtensions
     */
    public function __construct(
        public readonly string $extension,
        public readonly array $allowedExtensions,
    ) {
        parent::__construct(sprintf(
            'File type <strong>%s</strong> is not permitted. Allowed: %s',
            $extension,
            implode(', ', $allowedExtensions),
        ));
    }
}
