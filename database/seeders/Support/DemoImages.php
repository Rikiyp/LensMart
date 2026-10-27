<?php

namespace Database\Seeders\Support;

/**
 * Membuat aset gambar demo secara lokal sehingga aplikasi tidak bergantung
 * pada layanan gambar eksternal saat dijalankan.
 */
class DemoImages
{
    /**
     * @var array<string, array{0: int, 1: int, 2: int}>
     */
    private const ACCENTS = [
        'kamera-mirrorless' => [251, 191, 36],
        'kamera-dslr' => [56, 189, 248],
        'kamera-instan' => [244, 114, 182],
        'kamera-action' => [52, 211, 153],
        'lensa' => [167, 139, 250],
        'aksesori' => [251, 146, 60],
        'lighting-studio' => [253, 224, 71],
        'tripod-stabilizer' => [148, 163, 184],
        'tas-case' => [129, 140, 248],
        'drone' => [34, 211, 238],
        'audio' => [248, 113, 113],
    ];

    public static function productImage(string $path, string $categorySlug, int $variant = 0): string
    {
        $fullPath = storage_path('app/public/'.$path);

        if (! function_exists('imagecreatetruecolor')) {
            return $path;
        }

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0777, true);
        }

        if (file_exists($fullPath)) {
            return $path;
        }

        $width = 1200;
        $height = 900;
        $image = imagecreatetruecolor($width, $height);
        imageantialias($image, true);

        [$ar, $ag, $ab] = self::ACCENTS[$categorySlug] ?? [251, 191, 36];
        $shift = ($variant % 5) * 9;

        // Latar belakang gradasi gelap.
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / $height;
            $r = (int) (14 + ($ratio * 30));
            $g = (int) (14 + ($ratio * 28));
            $b = (int) (20 + ($ratio * 38));
            $color = imagecolorallocate($image, $r, $g, $b);
            imagefilledrectangle($image, 0, $y, $width, $y, $color);
        }

        // Cahaya aksen di belakang produk.
        $glow = imagecolorallocatealpha($image, min(255, $ar + 10), min(255, $ag + 10), min(255, $ab + 10), 102);
        for ($i = 0; $i < 7; $i++) {
            imagefilledellipse($image, 600 + ($variant % 3 - 1) * 25, 420, 560 + $i * 64, 420 + $i * 48, $glow);
        }

        $ink = imagecolorallocate($image, 20, 20, 26);
        $line = imagecolorallocate($image, 92, 92, 104);
        $accent = imagecolorallocate($image, $ar, $ag, $ab);
        $accentDark = imagecolorallocate($image, (int) max(20, $ar * .55), (int) max(20, $ag * .55), (int) max(20, $ab * .55));
        $white = imagecolorallocate($image, 248, 250, 252);

        $offsetX = (($variant % 4) - 1.5) * 18;

        if (in_array($categorySlug, ['kamera-mirrorless', 'kamera-dslr', 'kamera-instan', 'kamera-action'], true)) {
            self::drawCamera($image, $offsetX, $ink, $line, $accent, $accentDark, $white, $categorySlug === 'kamera-instan');
        } elseif ($categorySlug === 'lensa') {
            self::drawLens($image, $offsetX, $ink, $line, $accent, $accentDark, $white);
        } elseif ($categorySlug === 'lighting-studio') {
            self::drawLight($image, $offsetX, $ink, $line, $accent, $accentDark, $white);
        } elseif ($categorySlug === 'tripod-stabilizer') {
            self::drawTripod($image, $offsetX, $ink, $line, $accent, $accentDark, $white);
        } elseif ($categorySlug === 'tas-case') {
            self::drawBag($image, $offsetX, $ink, $line, $accent, $accentDark, $white);
        } elseif ($categorySlug === 'drone') {
            self::drawDrone($image, $offsetX, $ink, $line, $accent, $accentDark, $white);
        } elseif ($categorySlug === 'audio') {
            self::drawMicrophone($image, $offsetX, $ink, $line, $accent, $accentDark, $white);
        } else {
            self::drawAccessory($image, $offsetX, $ink, $line, $accent, $accentDark, $white);
        }

        // Label kecil agar gambar terasa seperti aset katalog.
        imagestring($image, 5, 64, 812, 'LENSMART', $accent);
        imagestring($image, 3, 64, 838, strtoupper(str_replace('-', ' ', $categorySlug)), $line);

        imagejpeg($image, $fullPath, 88);
        imagedestroy($image);

        return $path;
    }

    public static function storeLogo(string $path, string $initials, int $variant = 0): string
    {
        $fullPath = storage_path('app/public/'.$path);

        if (! function_exists('imagecreatetruecolor')) {
            return $path;
        }

        if (! is_dir(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0777, true);
        }

        if (file_exists($fullPath)) {
            return $path;
        }

        $size = 400;
        $image = imagecreatetruecolor($size, $size);
        imageantialias($image, true);

        $palette = [
            [16, 16, 20, 251, 191, 36],
            [28, 28, 33, 56, 189, 248],
            [35, 25, 40, 244, 114, 182],
        ];
        [$r, $g, $b, $ar, $ag, $ab] = $palette[$variant % count($palette)];

        for ($y = 0; $y < $size; $y++) {
            $ratio = $y / $size;
            $color = imagecolorallocate(
                $image,
                (int) ($r + $ratio * 24),
                (int) ($g + $ratio * 22),
                (int) ($b + $ratio * 28)
            );
            imagefilledrectangle($image, 0, $y, $size, $y, $color);
        }

        $accent = imagecolorallocate($image, $ar, $ag, $ab);
        $glow = imagecolorallocatealpha($image, $ar, $ag, $ab, 92);
        imagefilledellipse($image, 300, 90, 300, 300, $glow);
        imagefilledellipse($image, 110, 300, 180, 180, $glow);

        $font = 'C:\\Windows\\Fonts\\arialbd.ttf';
        if (file_exists($font)) {
            imagettftext($image, 128, 0, 72, 275, $accent, $font, strtoupper($initials));
        } else {
            imagestring($image, 5, 165, 185, strtoupper($initials), $accent);
        }

        imagejpeg($image, $fullPath, 90);
        imagedestroy($image);

        return $path;
    }

    private static function drawCamera($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white, bool $instant = false): void
    {
        $left = 330 + $offsetX;
        $top = 325;
        imagefilledrectangle($image, $left, $top, $left + 540, $top + 310, $ink);
        imagerectangle($image, $left, $top, $left + 540, $top + 310, $line);
        imagefilledrectangle($image, $left + 48, $top - 52, $left + 206, $top + 6, $ink);
        imagerectangle($image, $left + 48, $top - 52, $left + 206, $top + 6, $line);
        imagefilledrectangle($image, $left + 48, $top - 52, $left + 206, $top - 20, $accent);

        $centerX = $left + 280;
        $centerY = $top + 160;
        imagefilledellipse($image, $centerX, $centerY, 300, 300, $ink);
        imagefilledellipse($image, $centerX, $centerY, 238, 238, $line);
        imagefilledellipse($image, $centerX, $centerY, 156, 156, $accentDark);
        imagefilledellipse($image, $centerX - 12, $centerY - 14, 96, 96, $white);

        if ($instant) {
            imagefilledrectangle($image, $left + 360, $top + 52, $left + 470, $top + 130, $accent);
            imagefilledellipse($image, $left + 415, $top + 91, 60, 60, $white);
        } else {
            imagefilledrectangle($image, $left + 400, $top + 48, $left + 500, $top + 118, $accent);
            imagefilledellipse($image, $left + 450, $top + 83, 46, 46, $ink);
        }

        imagefilledrectangle($image, $left + 48, $top + 250, $left + 150, $top + 280, $accent);
    }

    private static function drawLens($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white): void
    {
        $centerX = 600 + $offsetX;
        $centerY = 445;
        imagefilledellipse($image, $centerX, $centerY, 560, 560, $ink);
        imagefilledellipse($image, $centerX, $centerY, 500, 500, $line);
        imagefilledellipse($image, $centerX, $centerY, 410, 410, $ink);
        imagefilledellipse($image, $centerX, $centerY, 300, 300, $accentDark);
        imagefilledellipse($image, $centerX, $centerY, 190, 190, $accent);
        imagefilledellipse($image, $centerX - 24, $centerY - 24, 92, 92, $white);

        for ($i = 0; $i < 8; $i++) {
            $angle = $i * 45;
            $x = $centerX + (int) (cos(deg2rad($angle)) * 220);
            $y = $centerY + (int) (sin(deg2rad($angle)) * 220);
            imagefilledellipse($image, $x, $y, 14, 14, $line);
        }
    }

    private static function drawLight($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white): void
    {
        $left = 430 + $offsetX;
        imagefilledpolygon($image, [$left + 170, 160, $left + 330, 160, $left + 280, 700, $left + 220, 700], $ink);
        imagefilledrectangle($image, $left, 150, $left + 500, 470, $ink);
        imagerectangle($image, $left, 150, $left + 500, 470, $line);
        imagefilledrectangle($image, $left + 36, 186, $left + 464, 434, $accentDark);
        imagefilledrectangle($image, $left + 62, 212, $left + 438, 408, $accent);
        for ($i = 0; $i < 4; $i++) {
            imagefilledellipse($image, $left + 125 + $i * 85, 310, 46, 46, $white);
        }
        imagefilledrectangle($image, $left + 40, 700, $left + 460, 724, $line);
    }

    private static function drawTripod($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white): void
    {
        $centerX = 600 + $offsetX;
        imagefilledpolygon($image, [$centerX - 16, 310, $centerX + 16, 310, $centerX + 210, 760, $centerX + 170, 760], $line);
        imagefilledpolygon($image, [$centerX - 16, 310, $centerX + 16, 310, $centerX - 210, 760, $centerX - 170, 760], $line);
        imagefilledpolygon($image, [$centerX - 12, 310, $centerX + 12, 310, $centerX + 70, 760, $centerX + 30, 760], $ink);
        imagefilledrectangle($image, $centerX - 110, 260, $centerX + 110, 320, $ink);
        imagefilledellipse($image, $centerX, 292, 240, 56, $accent);
        imagefilledrectangle($image, $centerX - 50, 196, $centerX + 50, 260, $line);
        imagefilledellipse($image, $centerX, 230, 100, 100, $accentDark);
        imagefilledellipse($image, $centerX, 230, 58, 58, $white);
        imagefilledrectangle($image, $centerX - 130, 748, $centerX + 130, 766, $line);
    }

    private static function drawBag($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white): void
    {
        $left = 390 + $offsetX;
        imagefilledrectangle($image, $left, 330, $left + 420, 690, $ink);
        imagerectangle($image, $left, 330, $left + 420, 690, $line);
        imagefilledarc($image, $left + 210, 330, 260, 270, 180, 360, $line, IMG_ARC_NOFILL);
        imagefilledrectangle($image, $left + 42, 430, $left + 378, 620, $accentDark);
        imagefilledrectangle($image, $left + 68, 456, $left + 352, 594, $accent);
        imagefilledrectangle($image, $left + 120, 410, $left + 300, 438, $white);
        imagefilledrectangle($image, $left + 120, 612, $left + 300, 640, $white);
        imagefilledellipse($image, $left + 210, 380, 56, 56, $accent);
    }

    private static function drawDrone($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white): void
    {
        $centerX = 600 + $offsetX;
        $centerY = 460;
        imagerectangle($image, $centerX - 130, $centerY - 70, $centerX + 130, $centerY + 80, $line);
        imagefilledrectangle($image, $centerX - 130, $centerY - 70, $centerX + 130, $centerY + 80, $ink);
        imagefilledellipse($image, $centerX, $centerY + 5, 120, 120, $accentDark);
        imagefilledellipse($image, $centerX, $centerY + 5, 56, 56, $accent);

        foreach ([[-1, -1], [1, -1], [-1, 1], [1, 1]] as [$dx, $dy]) {
            $armX = $centerX + $dx * 160;
            $armY = $centerY + $dy * 120;
            imageline($image, $centerX + $dx * 80, $centerY + $dy * 40, $armX, $armY, $line);
            imagefilledellipse($image, $armX, $armY, 190, 38, $line);
            imagefilledellipse($image, $armX, $armY, 168, 24, $accentDark);
            imagefilledellipse($image, $armX, $armY, 28, 28, $white);
        }
    }

    private static function drawMicrophone($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white): void
    {
        $centerX = 600 + $offsetX;
        imagefilledrectangle($image, $centerX - 95, 220, $centerX + 95, 560, $ink);
        imagefilledellipse($image, $centerX, 220, 190, 180, $line);
        imagefilledellipse($image, $centerX, 220, 160, 150, $accentDark);
        for ($y = 210; $y < 530; $y += 28) {
            imageline($image, $centerX - 80, $y, $centerX + 80, $y, $line);
        }
        imagefilledrectangle($image, $centerX - 18, 560, $centerX + 18, 690, $line);
        imagefilledellipse($image, $centerX, 700, 220, 46, $accent);
        imagefilledellipse($image, $centerX, 220, 64, 64, $white);
    }

    private static function drawAccessory($image, int $offsetX, int $ink, int $line, int $accent, int $accentDark, int $white): void
    {
        $left = 420 + $offsetX;
        imagefilledrectangle($image, $left, 320, $left + 360, 610, $ink);
        imagerectangle($image, $left, 320, $left + 360, 610, $line);
        imagefilledrectangle($image, $left + 34, 352, $left + 326, 578, $accentDark);
        imagefilledrectangle($image, $left + 58, 380, $left + 302, 540, $accent);
        for ($i = 0; $i < 4; $i++) {
            imagefilledrectangle($image, $left + 84 + $i * 58, 390, $left + 116 + $i * 58, 530, $white);
        }
        imagefilledrectangle($image, $left + 88, 300, $left + 272, 322, $line);
    }
}
