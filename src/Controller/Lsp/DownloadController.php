<?php

declare(strict_types=1);

namespace App\Controller\Lsp;

use App\Lsp\Download\DownloadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class DownloadController extends AbstractController
{
    public function __construct(
        private readonly DownloadService $downloads,
    ) {}

    public function download(int $id): Response
    {
        return $this->downloads->serveById($id, countDownload: true)
            ?? new Response('File not found.', Response::HTTP_NOT_FOUND);
    }
}
