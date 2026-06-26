<?php

declare(strict_types=1);

namespace App\Lsp\Project;

final class ThumbnailGenerator
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'gif', 'bmp', 'png'];
    private const WIDTH = 300;

    public function __construct(private readonly string $lspDataDir) {}

    /**
     * Returns a base64-encoded PNG data URI for the image, or null when
     * the file isn't an image, can't be decoded, or is narrower than WIDTH.
     */
    public function generate(int $fileId, string $filename): ?string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if (!in_array($ext, self::IMAGE_EXTENSIONS, true)) {
            return null;
        }

        $path = $this->lspDataDir . '/' . $fileId;
        if (!is_file($path)) {
            return null;
        }

        $image = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'gif'         => @imagecreatefromgif($path),
            'bmp'         => @imagecreatefrombmp($path),
            'png'         => @imagecreatefrompng($path),
        };

        if ($image === false) {
            return null;
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        if ($origWidth < self::WIDTH) {
            return null;
        }

        $height = (int) (($origHeight * self::WIDTH) / $origWidth);
        $thumb = imagecreatetruecolor(self::WIDTH, $height);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        imagecopyresampled($thumb, $image, 0, 0, 0, 0, self::WIDTH, $height, $origWidth, $origHeight);

        ob_start();
        imagepng($thumb);
        $png = ob_get_clean();

        return 'data:image/png;base64,' . base64_encode((string) $png);
    }
}
