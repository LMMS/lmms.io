<?php

declare(strict_types=1);

namespace App\Lsp\WebResources;

use App\Lsp\Project\ProjectRepository;

/**
 * Builds the XML index consumed by the LMMS desktop client's web-resources browser.
 * The shape of this document is a public protocol; treat changes as breaking.
 */
final class WebResourcesFeed
{
    public function __construct(private readonly ProjectRepository $files) {}

    public function buildIndex(): string
    {
        $xml = $this->preamble() . '<webresources>';

        foreach ($this->files->listForWebResources() as $row) {
            $xml .= '<file>'
                . '<name>' . htmlspecialchars((string) $row['fname'], ENT_COMPAT, 'UTF-8') . '</name>'
                . '<hash>' . $row['hash'] . '</hash>'
                . '<size>' . $row['size'] . '</size>'
                . '<date>' . $row['date'] . '</date>'
                . '<author>' . htmlspecialchars((string) $row['author'], ENT_COMPAT, 'UTF-8') . '</author>'
                . '<dir>' . htmlspecialchars($row['catname'] . '/' . $row['subcatname'], ENT_COMPAT, 'UTF-8') . '</dir>'
                . '</file>';
        }

        return $xml . '</webresources>';
    }

    public function buildAccessDeniedNotice(): string
    {
        return $this->preamble()
            . '<error>Please contact the LMMS development team for API access</error>';
    }

    private function preamble(): string
    {
        return '<?xml version="1.0"?>' . "\n"
            . '<!DOCTYPE lmms-webresources-index>' . "\n";
    }
}
