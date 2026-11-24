<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class LspExtension extends AbstractExtension
{
    private const UNITS = [
        'year'   => 31536000,
        'month'  => 2592000,
        'week'   => 604800,
        'day'    => 86400,
        'hour'   => 3600,
        'minute' => 60,
    ];

    public function getFilters(): array
    {
        return [
            new TwigFilter('lsp_ago', $this->ago(...)),
            new TwigFilter('lsp_stem', $this->stem(...)),
            new TwigFilter('lsp_ext', $this->extension(...)),
            new TwigFilter('lsp_size', $this->size(...)),
        ];
    }

    /** "3 days ago" for a database timestamp; "just now" under a minute */
    public function ago(?string $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        $seconds = time() - strtotime($date);

        foreach (self::UNITS as $unit => $length) {
            $amount = intdiv($seconds, $length);
            if ($amount >= 1) {
                return $amount . ' ' . $unit . ($amount > 1 ? 's' : '') . ' ago';
            }
        }

        return 'just now';
    }

    /** "beat.tar.gz" → "beat" */
    public function stem(string $filename): string
    {
        $ext = $this->extension($filename);

        return $ext === '' ? $filename : substr($filename, 0, -strlen($ext) - 1);
    }

    /** "beat.tar.gz" → "tar.gz", "beat.mmpz" → "mmpz" */
    public function extension(string $filename): string
    {
        if (preg_match('/\.(tar\.\w+|\w+)$/i', $filename, $match) !== 1) {
            return '';
        }

        return strtolower($match[1]);
    }

    public function size(int|string $bytes): string
    {
        $bytes = (int) $bytes;

        return match (true) {
            $bytes >= 1048576 => round($bytes / 1048576, 1) . ' MB',
            $bytes >= 1024    => round($bytes / 1024) . ' KB',
            default           => $bytes . ' B',
        };
    }
}
