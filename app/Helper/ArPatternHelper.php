<?php

namespace App\Helper;

use RuntimeException;

class ArPatternHelper
{
    private const FULL_MARKER_SIZE = 512;

    private const PATTERN_SIZE = 16;

    private const WHITE_MARGIN = 0.1;

    private const DEFAULT_PATTERN_RATIO = 0.5;

    /**
     * Convert an uploaded image into AR.js .patt file content.
     */
    public static function encodeImageToPattern(string $imagePath): string
    {
        self::ensureGdAvailable();
        $sourceImage = self::loadImage($imagePath);
        $baseImage = null;

        try {
            $baseImage = imagecreatetruecolor(self::PATTERN_SIZE, self::PATTERN_SIZE);
            $white = imagecolorallocate($baseImage, 255, 255, 255);
            imagefill($baseImage, 0, 0, $white);

            imagecopyresampled(
                $baseImage, $sourceImage,
                0, 0, 0, 0,
                self::PATTERN_SIZE, self::PATTERN_SIZE,
                imagesx($sourceImage), imagesy($sourceImage)
            );

            $blocks = [];
            foreach ([0, -90, -180, -270] as $rotation) {
                $rotated = self::rotateImage($baseImage, $rotation);
                $blocks[] = self::encodeOrientation($rotated);
                imagedestroy($rotated);
            }

            return implode("\n", $blocks)."\n";
        } finally {
            if ($baseImage !== null) {
                imagedestroy($baseImage);
            }
            imagedestroy($sourceImage);
        }
    }

    /**
     * Build a print-ready marker PNG with white margin, black border, inner image.
     */
    public static function buildFullMarkerPng(
        string $imagePath,
        float $patternRatio = self::DEFAULT_PATTERN_RATIO,
        int $size = self::FULL_MARKER_SIZE,
        string $borderColor = 'black'
    ): string {
        self::ensureGdAvailable();
        $sourceImage = self::loadImage($imagePath);
        $canvas = imagecreatetruecolor($size, $size);

        [$borderR, $borderG, $borderB] = self::resolveBorderColor($borderColor);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        $blackBorder = imagecolorallocate($canvas, $borderR, $borderG, $borderB);

        imagefill($canvas, 0, 0, $white);

        $whiteMargin = self::WHITE_MARGIN;
        $blackMargin = (1 - 2 * $whiteMargin) * ((1 - $patternRatio) / 2);
        $innerMargin = $whiteMargin + $blackMargin;

        $outerX = (int) round($whiteMargin * $size);
        $outerY = (int) round($whiteMargin * $size);
        $outerW = max(1, (int) round((1 - 2 * $whiteMargin) * $size));
        $outerH = max(1, (int) round((1 - 2 * $whiteMargin) * $size));
        imagefilledrectangle($canvas, $outerX, $outerY, $outerX + $outerW - 1, $outerY + $outerH - 1, $blackBorder);

        $innerX = (int) round($innerMargin * $size);
        $innerY = (int) round($innerMargin * $size);
        $innerW = max(1, (int) round((1 - 2 * $innerMargin) * $size));
        $innerH = max(1, (int) round((1 - 2 * $innerMargin) * $size));
        imagefilledrectangle($canvas, $innerX, $innerY, $innerX + $innerW - 1, $innerY + $innerH - 1, $white);
        imagecopyresampled($canvas, $sourceImage, $innerX, $innerY, 0, 0, $innerW, $innerH,
            imagesx($sourceImage), imagesy($sourceImage));

        ob_start();
        imagepng($canvas);
        $pngBinary = ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($sourceImage);

        return $pngBinary ?: throw new RuntimeException('Gagal mengenkode marker AR ke PNG.');
    }

    /** Sisi grid modul pada sumber marker (ganjil supaya area logo simetris). */
    private const GRID = 11;

    /** Lebar satu modul dalam pixel. Ukuran sumber = GRID * MODULE. */
    private const MODULE = 28;

    /**
     * Susun sumber marker auto-generate: pola modul mirip QR dengan logo besar di tengah.
     *
     * Layout grid 11×11 modul:
     *   - cincin terluar dibiarkan putih (quiet zone, supaya pola tidak menyatu
     *     dengan bingkai hitam yang ditambahkan buildFullMarkerPng)
     *   - tiga kotak "mata" di pojok kiri-atas, kanan-atas, kiri-bawah — pojok
     *     kanan-bawah sengaja dikosongkan supaya orientasi marker tidak ambigu
     *     (AR.js mencocokkan pola pada 4 rotasi)
     *   - 5×5 modul di tengah untuk logo
     *   - sisanya diisi hitam/putih dari hash $seed, jadi tiap Marker ID punya
     *     pola unik tapi selalu sama setiap kali digenerate ulang
     *
     * Method terpisah dari buildFullMarkerPng() karena butuh alpha blending
     * (PNG transparan kalau tidak jadi artefak hitam) dan menjaga rasio aspek logo.
     *
     * Hasilnya dipakai sebagai input encodeImageToPattern() + buildFullMarkerPng().
     */
    public static function buildLogoMarkerSource(string $logoPath, string $seed = ''): string
    {
        self::ensureGdAvailable();

        $grid = self::GRID;
        $module = self::MODULE;
        $size = $grid * $module;

        $logo = self::loadImage($logoPath);
        $canvas = imagecreatetruecolor($size, $size);

        try {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            $black = imagecolorallocate($canvas, 0, 0, 0);
            imagefilledrectangle($canvas, 0, 0, $size - 1, $size - 1, $white);

            // Area logo: 5×5 modul di tengah (indeks 3..7 pada grid 11).
            $logoFrom = (int) (($grid - 5) / 2);
            $logoTo = $logoFrom + 4;

            // Mata QR 2×2 modul di tiga pojok area aktif (indeks 1..9).
            $eyes = [[1, 1], [1, $grid - 3], [$grid - 3, 1]];

            $bits = self::patternBits($seed);
            $bit = 0;

            for ($row = 1; $row < $grid - 1; $row++) {
                for ($col = 1; $col < $grid - 1; $col++) {
                    if ($row >= $logoFrom && $row <= $logoTo && $col >= $logoFrom && $col <= $logoTo) {
                        continue;
                    }

                    $isEye = false;
                    foreach ($eyes as [$eyeRow, $eyeCol]) {
                        if ($row >= $eyeRow && $row <= $eyeRow + 1 && $col >= $eyeCol && $col <= $eyeCol + 1) {
                            $isEye = true;
                            break;
                        }
                    }

                    $fill = $isEye || ($bits[$bit++ % strlen($bits)] === '1');
                    if ($fill) {
                        imagefilledrectangle(
                            $canvas,
                            $col * $module, $row * $module,
                            ($col + 1) * $module - 1, ($row + 1) * $module - 1,
                            $black
                        );
                    }
                }
            }

            // Logo menutupi seluruh area 5×5 modul, rasio aspek dijaga.
            imagealphablending($canvas, true);
            $box = 5 * $module;
            $logoW = imagesx($logo);
            $logoH = imagesy($logo);
            $scale = min($box / $logoW, $box / $logoH);
            $targetW = max(1, (int) round($logoW * $scale));
            $targetH = max(1, (int) round($logoH * $scale));
            $originX = $logoFrom * $module;

            // Latar putih di bawah logo supaya logo transparan tetap kontras.
            imagefilledrectangle($canvas, $originX, $originX, $originX + $box - 1, $originX + $box - 1, $white);
            imagecopyresampled(
                $canvas, $logo,
                $originX + (int) round(($box - $targetW) / 2),
                $originX + (int) round(($box - $targetH) / 2),
                0, 0,
                $targetW, $targetH,
                $logoW, $logoH
            );

            ob_start();
            imagepng($canvas);
            $pngBinary = ob_get_clean();

            return $pngBinary ?: throw new RuntimeException('Gagal menyusun marker dari logo.');
        } finally {
            imagedestroy($canvas);
            imagedestroy($logo);
        }
    }

    /** Deret bit deterministik dari seed; seed kosong tetap menghasilkan pola valid. */
    private static function patternBits(string $seed): string
    {
        $bits = '';
        foreach (str_split(hash('sha256', $seed), 1) as $hex) {
            $bits .= str_pad(base_convert($hex, 16, 2), 4, '0', STR_PAD_LEFT);
        }

        return $bits;
    }

    // --- private helpers ---

    private static function ensureGdAvailable(): void
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new RuntimeException('Ekstensi GD tidak tersedia.');
        }
    }

    private static function loadImage(string $imagePath): \GdImage
    {
        $contents = @file_get_contents($imagePath)
            ?: throw new RuntimeException('Gagal membaca file gambar marker.');
        $img = @imagecreatefromstring($contents)
            ?: throw new RuntimeException('Format gambar marker tidak didukung.');

        return $img;
    }

    private static function resolveBorderColor(string $borderColor): array
    {
        $n = strtolower(trim($borderColor));
        if ($n === '' || $n === 'black') {
            return [0, 0, 0];
        }
        if ($n === 'white') {
            return [255, 255, 255];
        }
        if (preg_match('/^#([0-9a-f]{6})$/i', $n, $m)) {
            return [hexdec($m[1][0].$m[1][1]), hexdec($m[1][2].$m[1][3]), hexdec($m[1][4].$m[1][5])];
        }

        return [0, 0, 0];
    }

    private static function rotateImage(\GdImage $image, int $rotation): \GdImage
    {
        $white = imagecolorallocate($image, 255, 255, 255);
        $rotated = imagerotate($image, $rotation, $white)
            ?: throw new RuntimeException('Gagal memutar gambar marker.');
        if (imagesx($rotated) === self::PATTERN_SIZE && imagesy($rotated) === self::PATTERN_SIZE) {
            return $rotated;
        }
        $normalized = imagecreatetruecolor(self::PATTERN_SIZE, self::PATTERN_SIZE);
        imagefill($normalized, 0, 0, $white);
        imagecopyresampled($normalized, $rotated, 0, 0, 0, 0,
            self::PATTERN_SIZE, self::PATTERN_SIZE, imagesx($rotated), imagesy($rotated));
        imagedestroy($rotated);

        return $normalized;
    }

    private static function encodeOrientation(\GdImage $image): string
    {
        $rows = [];
        foreach (['blue', 'green', 'red'] as $channel) {
            for ($y = 0; $y < self::PATTERN_SIZE; $y++) {
                $values = [];
                for ($x = 0; $x < self::PATTERN_SIZE; $x++) {
                    $rgb = imagecolorat($image, $x, $y);
                    $values[] = str_pad((string) self::extractChannel($rgb, $channel), 3, ' ', STR_PAD_LEFT);
                }
                $rows[] = implode(' ', $values);
            }
        }

        return implode("\n", $rows);
    }

    private static function extractChannel(int $rgb, string $channel): int
    {
        return match ($channel) {
            'red' => ($rgb >> 16) & 0xFF,
            'green' => ($rgb >> 8) & 0xFF,
            default => $rgb & 0xFF,
        };
    }
}
