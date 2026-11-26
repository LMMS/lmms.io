<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\Extension\HtmlExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HtmlExtensionTest extends TestCase
{
    #[DataProvider('headingsProvider')]
    public function testNestHeadings(string $html, int $top, string $expected): void
    {
        self::assertSame($expected, (new HtmlExtension())->nestHeadings($html, $top));
    }

    public static function headingsProvider(): array
    {
        return [
            'no headings'        => ['<p>hi</p>', 2, '<p>hi</p>'],
            'shifts down'        => ['<h1>a</h1><h2>b</h2>', 2, '<h2>a</h2><h3>b</h3>'],
            'shifts up'          => ['<h5>a</h5>', 4, '<h4>a</h4>'],
            'closes gaps'        => ['<h1>a</h1><h3>b</h3><h3>c</h3>', 2, '<h2>a</h2><h3>b</h3><h3>c</h3>'],
            'keeps attributes'   => ['<h2 dir="auto" class="x">a</h2>', 4, '<h4 dir="auto" class="x">a</h4>'],
            'clamps at h6'       => ['<h1>a</h1><h2>b</h2><h3>c</h3>', 5, '<h5>a</h5><h6>b</h6><h6>c</h6>'],
            'ignores lookalikes' => ['<hr><header><h1>a</h1></header>', 3, '<hr><header><h3>a</h3></header>'],
            'stray closing tag'  => ['<h1>a</h1></h4>', 2, '<h2>a</h2></h4>'],
        ];
    }
}
