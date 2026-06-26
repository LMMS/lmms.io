<?php

declare(strict_types=1);

namespace App\Lsp\Project;

final class ProjectMetadata
{
    public function __construct(private readonly string $lspDataDir) {}

    public function creatorVersion(int $fileId, string $filename): ?string
    {
        $path = $this->lspDataDir . '/' . $fileId;
        if (!is_file($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $xml = match ($ext) {
            'mmp'  => @file_get_contents($path),
            'mmpz' => $this->gzDecode($path),
            default => null,
        };

        if ($xml === null || $xml === false || $xml === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $project = simplexml_load_string($xml);
        libxml_use_internal_errors($previous);

        if ($project === false) {
            return null;
        }

        $version = (string) ($project['creatorversion'] ?? '');
        return $version !== '' ? $version : null;
    }

    private function gzDecode(string $path): string|false
    {
        $data = @file_get_contents($path);
        if ($data === false) {
            return false;
        }
        return @gzdecode($data);
    }
}
