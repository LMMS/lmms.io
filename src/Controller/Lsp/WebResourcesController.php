<?php

declare(strict_types=1);

namespace App\Controller\Lsp;

use App\Lsp\Download\DownloadService;
use App\Lsp\WebResources\WebResourcesFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class WebResourcesController extends AbstractController
{
    public function __construct(
        private readonly WebResourcesFeed $feed,
        private readonly DownloadService $downloads,
    ) {}

    public function webResources(Request $request): Response
    {
        $mode = $request->query->get('download', '');

        if ($mode === 'resource' && $request->query->get('id') !== null) {
            return $this->downloads->serveByHash((string) $request->query->get('id'), countDownload: false)
                ?? new Response('File not found.', Response::HTTP_NOT_FOUND);
        }

        $xml = $mode === 'index'
            ? $this->feed->buildIndex()
            : $this->feed->buildAccessDeniedNotice();

        return new Response($xml, Response::HTTP_OK, [
            'Content-Type'        => 'text/xml',
            'Content-Description' => 'LMMS WebResources Index',
        ]);
    }
}
