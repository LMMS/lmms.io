<?php

declare(strict_types=1);

namespace App\Lsp\Download;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

final class DownloadService
{
    public function __construct(
        private readonly DownloadRepository $downloads,
        private readonly string $lspDataDir,
    ) {}

    /**
     * Browser download: looks up the project by id, optionally bumps the
     * popularity counter, and streams the blob as an attachment. Returns
     * null when either the row or the on-disk blob is missing.
     */
    public function serveById(int $id, bool $countDownload): ?BinaryFileResponse
    {
        $filename = $this->downloads->findFilenameById($id);

        if ($filename === null) {
            return null;
        }

        return $this->build($id, $filename, $countDownload);
    }

    /**
     * LMMS desktop client download: looks up by content hash. Counting is
     * exposed as a parameter so the asymmetry vs serveById is explicit
     * rather than hidden in two different controller paths.
     */
    public function serveByHash(string $hash, bool $countDownload): ?BinaryFileResponse
    {
        $row = $this->downloads->findByHash($hash);

        if ($row === null) {
            return null;
        }

        return $this->build($row['id'], $row['filename'], $countDownload);
    }

    private function build(int $id, string $filename, bool $countDownload): ?BinaryFileResponse
    {
        $path = $this->lspDataDir . '/' . $id;

        if (!file_exists($path)) {
            return null;
        }

        if ($countDownload) {
            $this->downloads->incrementCounter($id);
        }

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        return $response;
    }
}
