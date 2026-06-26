<?php

declare(strict_types=1);

namespace App\Lsp\Legacy;

use Symfony\Component\HttpFoundation\Request;

final readonly class LegacyRedirectResolver
{
    /**
     * Maps the old `index.php?...` query shorthands to the modern named routes.
     * Returns `[routeName, params]` for the matching shorthand, or null when
     * the request should fall through to the normal browse handler.
     */
    public function resolve(Request $request): ?array
    {
        $q = $request->query;
        $file = $q->get('file');
        $fileId = $file !== null ? (int) $file : null;

        if ($q->get('comment') === 'add' && $fileId !== null) {
            return ['lsp_comment', ['id' => $fileId]];
        }

        if ($q->get('content') === 'delete' && $fileId !== null) {
            return ['lsp_delete', ['id' => $fileId]];
        }

        if ($q->get('content') === 'update' && $fileId !== null) {
            return ['lsp_edit', ['id' => $fileId]];
        }

        if ($q->get('content') === 'add') {
            return ['lsp_add', []];
        }

        $rate = (int) $q->get('rate', 0);
        if ($rate >= 1 && $rate <= 5 && $fileId !== null) {
            return ['lsp_rate', ['id' => $fileId, 'stars' => $rate]];
        }

        if ($q->get('account') === 'settings') {
            return ['lsp_settings', []];
        }

        if ($q->get('action') === 'register') {
            return ['lsp_register', []];
        }

        if ($q->get('action') === 'show' && $fileId !== null) {
            return ['lsp_show', ['id' => $fileId]];
        }

        return null;
    }
}
