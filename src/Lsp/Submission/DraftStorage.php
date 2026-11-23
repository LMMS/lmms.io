<?php

declare(strict_types=1);

namespace App\Lsp\Submission;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class DraftStorage
{
    public function __construct(
        private FileTypeCatalog $fileTypes,
        private string $lspDataDir,
    ) {}

    public function open(UploadedFile $upload): string
    {
        $extension = $this->fileTypes->extensionOf($upload->getClientOriginalName());

        if (!$this->fileTypes->permits($extension)) {
            throw new UnsupportedFileTypeException($extension, $this->fileTypes->allowedExtensions());
        }

        $tmpName = bin2hex(random_bytes(16));
        $upload->move($this->lspDataDir, $tmpName);

        return $this->lspDataDir . '/' . $tmpName;
    }

    public function assertResumable(string $tmpPath): void
    {
        if (str_contains($tmpPath, '..')
            || !file_exists($tmpPath)
            || realpath(dirname($tmpPath)) !== realpath($this->lspDataDir)
        ) {
            throw new InvalidDraftSubmissionException();
        }
    }

    public function hash(string $tmpPath): string
    {
        return sha1_file($tmpPath);
    }

    public function promoteTo(string $tmpPath, string $finalPath): void
    {
        rename($tmpPath, $finalPath);
    }
}
