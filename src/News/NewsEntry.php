<?php

namespace App\News;

final class NewsEntry
{
    public function __construct(
        public readonly \DateTimeImmutable $date,
        public readonly string             $title,
        public readonly string             $url,
        public readonly string             $author,
        public readonly string             $bodyHtml,
    ) {
    }

    public function slug(): string
    {
        return $this->date->format('Y-m-d');
    }
}
