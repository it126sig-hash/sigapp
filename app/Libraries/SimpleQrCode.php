<?php

namespace App\Libraries;

use InvalidArgumentException;

class SimpleQrCode
{
    private const ECL_MEDIUM_TABLE_INDEX = 1;
    private const ECL_MEDIUM_FORMAT_BITS = 0;

    private const ECC_CODEWORDS_PER_BLOCK = [
        [7, 10, 15, 20, 26, 18, 20, 24, 30, 18, 20, 24, 26, 30, 22, 24, 28, 30, 28, 28, 28, 28, 30, 30, 26, 28, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30],
        [10, 16, 26, 18, 24, 16, 18, 22, 22, 26, 30, 22, 22, 24, 24, 28, 28, 26, 26, 26, 26, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28, 28],
        [13, 22, 18, 26, 18, 24, 18, 22, 20, 24, 28, 26, 24, 20, 30, 24, 28, 28, 26, 30, 28, 30, 30, 30, 30, 28, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30],
        [17, 28, 22, 16, 22, 28, 26, 26, 24, 28, 24, 28, 22, 24, 24, 30, 28, 28, 26, 28, 30, 28, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30, 30],
    ];

    private const NUM_ERROR_CORRECTION_BLOCKS = [
        [1, 1, 1, 1, 1, 2, 2, 2, 2, 4, 4, 4, 4, 4, 6, 6, 6, 6, 7, 8, 8, 9, 9, 10, 12, 12, 12, 13, 14, 15, 16, 17, 18, 19, 19, 20, 21, 22, 24, 25],
        [1, 1, 1, 2, 2, 4, 4, 4, 5, 5, 5, 8, 9, 9, 10, 10, 11, 13, 14, 16, 17, 17, 18, 20, 21, 23, 25, 26, 28, 29, 31, 33, 35, 37, 38, 40, 43, 45, 47, 49],
        [1, 1, 2, 2, 4, 4, 6, 6, 8, 8, 8, 10, 12, 16, 12, 17, 16, 18, 21, 20, 23, 23, 25, 27, 29, 34, 34, 35, 38, 40, 43, 45, 48, 51, 53, 56, 59, 62, 65, 68],
        [1, 1, 2, 4, 4, 4, 5, 6, 8, 8, 11, 11, 16, 16, 18, 16, 19, 21, 25, 25, 25, 34, 30, 32, 35, 37, 40, 42, 45, 48, 51, 54, 57, 60, 63, 66, 70, 74, 77, 81],
    ];

    private const TOTAL_CODEWORDS = [
        26, 44, 70, 100, 134, 172, 196, 242, 292, 346,
        404, 466, 532, 581, 655, 733, 815, 901, 991, 1085,
        1156, 1258, 1364, 1474, 1588, 1706, 1828, 1921, 2051, 2185,
        2323, 2465, 2611, 2761, 2876, 3034, 3196, 3362, 3532, 3706,
    ];

    private int $version;
    private int $size;
    private array $modules = [];
    private array $isFunction = [];

    public static function encodeText(string $text): self
    {
        $bytes = array_values(unpack('C*', $text) ?: []);
        $version = self::chooseVersion(count($bytes));
        $dataCodewords = self::createDataCodewords($bytes, $version);

        return new self($version, self::addEccAndInterleave($dataCodewords, $version));
    }

    private function __construct(int $version, array $codewords)
    {
        $this->version = $version;
        $this->size = $version * 4 + 17;

        for ($y = 0; $y < $this->size; $y++) {
            $this->modules[$y] = array_fill(0, $this->size, false);
            $this->isFunction[$y] = array_fill(0, $this->size, false);
        }

        $this->drawFunctionPatterns();
        $this->drawCodewords($codewords, 0);
        $this->drawFormatBits(0);
    }

    public function toPng(int $scale = 8, int $border = 4): string
    {
        $dimension = ($this->size + $border * 2) * $scale;
        $image = imagecreatetruecolor($dimension, $dimension);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 17, 24, 39);

        imagefill($image, 0, 0, $white);

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                if (! $this->modules[$y][$x]) {
                    continue;
                }

                imagefilledrectangle(
                    $image,
                    ($x + $border) * $scale,
                    ($y + $border) * $scale,
                    ($x + $border + 1) * $scale - 1,
                    ($y + $border + 1) * $scale - 1,
                    $black
                );
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return $png === false ? '' : $png;
    }

    private static function chooseVersion(int $byteLength): int
    {
        for ($version = 1; $version <= 40; $version++) {
            $charCountBits = $version <= 9 ? 8 : 16;
            $neededBits = 4 + $charCountBits + $byteLength * 8;
            if ($neededBits <= self::getNumDataCodewords($version) * 8) {
                return $version;
            }
        }

        throw new InvalidArgumentException('Data QR terlalu panjang.');
    }

    private static function createDataCodewords(array $bytes, int $version): array
    {
        $bits = [];
        self::appendBits($bits, 0x4, 4);
        self::appendBits($bits, count($bytes), $version <= 9 ? 8 : 16);

        foreach ($bytes as $byte) {
            self::appendBits($bits, $byte, 8);
        }

        $capacityBits = self::getNumDataCodewords($version) * 8;
        self::appendBits($bits, 0, min(4, $capacityBits - count($bits)));
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $pad = [0xEC, 0x11];
        $padIndex = 0;
        while (count($bits) < $capacityBits) {
            self::appendBits($bits, $pad[$padIndex % 2], 8);
            $padIndex++;
        }

        $result = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $byte = 0;
            for ($j = 0; $j < 8; $j++) {
                $byte = ($byte << 1) | $bits[$i + $j];
            }
            $result[] = $byte;
        }

        return $result;
    }

    private static function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    private static function addEccAndInterleave(array $data, int $version): array
    {
        $numBlocks = self::NUM_ERROR_CORRECTION_BLOCKS[self::ECL_MEDIUM_TABLE_INDEX][$version - 1];
        $blockEccLen = self::ECC_CODEWORDS_PER_BLOCK[self::ECL_MEDIUM_TABLE_INDEX][$version - 1];
        $rawCodewords = self::TOTAL_CODEWORDS[$version - 1];
        $numShortBlocks = $numBlocks - $rawCodewords % $numBlocks;
        $shortBlockDataLen = intdiv($rawCodewords, $numBlocks) - $blockEccLen;
        $rsDivisor = self::reedSolomonComputeDivisor($blockEccLen);
        $blocks = [];
        $offset = 0;

        for ($i = 0; $i < $numBlocks; $i++) {
            $dataLen = $shortBlockDataLen + ($i < $numShortBlocks ? 0 : 1);
            $dataBlock = array_slice($data, $offset, $dataLen);
            $offset += $dataLen;
            $ecc = self::reedSolomonComputeRemainder($dataBlock, $rsDivisor);
            $blocks[] = array_merge($dataBlock, $i < $numShortBlocks ? [0] : [], $ecc);
        }

        $result = [];
        $maxLen = count($blocks[count($blocks) - 1]);
        for ($i = 0; $i < $maxLen; $i++) {
            foreach ($blocks as $j => $block) {
                if ($i !== $shortBlockDataLen || $j >= $numShortBlocks) {
                    if (isset($block[$i])) {
                        $result[] = $block[$i];
                    }
                }
            }
        }

        return $result;
    }

    private static function getNumDataCodewords(int $version): int
    {
        return self::TOTAL_CODEWORDS[$version - 1]
            - self::ECC_CODEWORDS_PER_BLOCK[self::ECL_MEDIUM_TABLE_INDEX][$version - 1]
            * self::NUM_ERROR_CORRECTION_BLOCKS[self::ECL_MEDIUM_TABLE_INDEX][$version - 1];
    }

    private function drawFunctionPatterns(): void
    {
        $this->drawFinderPattern(3, 3);
        $this->drawFinderPattern($this->size - 4, 3);
        $this->drawFinderPattern(3, $this->size - 4);

        for ($i = 0; $i < $this->size; $i++) {
            if (! $this->isFunction[6][$i]) {
                $this->setFunctionModule($i, 6, $i % 2 === 0);
            }
            if (! $this->isFunction[$i][6]) {
                $this->setFunctionModule(6, $i, $i % 2 === 0);
            }
        }

        $positions = $this->alignmentPatternPositions();
        foreach ($positions as $x) {
            foreach ($positions as $y) {
                if (! $this->isFunction[$y][$x]) {
                    $this->drawAlignmentPattern($x, $y);
                }
            }
        }

        $this->drawFormatBits(0);
        $this->drawVersionBits();
        $this->setFunctionModule(8, $this->size - 8, true);
    }

    private function drawFinderPattern(int $cx, int $cy): void
    {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $x = $cx + $dx;
                $y = $cy + $dy;
                if ($x < 0 || $x >= $this->size || $y < 0 || $y >= $this->size) {
                    continue;
                }

                $dist = max(abs($dx), abs($dy));
                $this->setFunctionModule($x, $y, $dist !== 2 && $dist !== 4);
            }
        }
    }

    private function drawAlignmentPattern(int $cx, int $cy): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $this->setFunctionModule($cx + $dx, $cy + $dy, max(abs($dx), abs($dy)) !== 1);
            }
        }
    }

    private function drawFormatBits(int $mask): void
    {
        $data = (self::ECL_MEDIUM_FORMAT_BITS << 3) | $mask;
        $rem = $data;
        for ($i = 0; $i < 10; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 9) * 0x537);
        }
        $bits = (($data << 10) | $rem) ^ 0x5412;

        for ($i = 0; $i <= 5; $i++) {
            $this->setFunctionModule(8, $i, (($bits >> $i) & 1) !== 0);
        }
        $this->setFunctionModule(8, 7, (($bits >> 6) & 1) !== 0);
        $this->setFunctionModule(8, 8, (($bits >> 7) & 1) !== 0);
        $this->setFunctionModule(7, 8, (($bits >> 8) & 1) !== 0);
        for ($i = 9; $i < 15; $i++) {
            $this->setFunctionModule(14 - $i, 8, (($bits >> $i) & 1) !== 0);
        }
        for ($i = 0; $i < 8; $i++) {
            $this->setFunctionModule($this->size - 1 - $i, 8, (($bits >> $i) & 1) !== 0);
        }
        for ($i = 8; $i < 15; $i++) {
            $this->setFunctionModule(8, $this->size - 15 + $i, (($bits >> $i) & 1) !== 0);
        }
    }

    private function drawVersionBits(): void
    {
        if ($this->version < 7) {
            return;
        }

        $rem = $this->version;
        for ($i = 0; $i < 12; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 11) * 0x1F25);
        }
        $bits = ($this->version << 12) | $rem;

        for ($i = 0; $i < 18; $i++) {
            $bit = (($bits >> $i) & 1) !== 0;
            $a = $this->size - 11 + $i % 3;
            $b = intdiv($i, 3);
            $this->setFunctionModule($a, $b, $bit);
            $this->setFunctionModule($b, $a, $bit);
        }
    }

    private function drawCodewords(array $codewords, int $mask): void
    {
        $bitIndex = 0;

        for ($right = $this->size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }

            for ($vert = 0; $vert < $this->size; $vert++) {
                $y = (($right + 1) & 2) === 0 ? $this->size - 1 - $vert : $vert;
                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;
                    if ($this->isFunction[$y][$x]) {
                        continue;
                    }

                    $bit = false;
                    if ($bitIndex < count($codewords) * 8) {
                        $bit = (($codewords[intdiv($bitIndex, 8)] >> (7 - ($bitIndex % 8))) & 1) !== 0;
                    }

                    $this->modules[$y][$x] = $bit ^ $this->maskBit($mask, $x, $y);
                    $bitIndex++;
                }
            }
        }
    }

    private function maskBit(int $mask, int $x, int $y): bool
    {
        return (($x + $y) % 2) === 0;
    }

    private function setFunctionModule(int $x, int $y, bool $isDark): void
    {
        $this->modules[$y][$x] = $isDark;
        $this->isFunction[$y][$x] = true;
    }

    private function alignmentPatternPositions(): array
    {
        if ($this->version === 1) {
            return [];
        }

        $numAlign = intdiv($this->version, 7) + 2;
        $step = $this->version === 32
            ? 26
            : intdiv($this->version * 4 + $numAlign * 2 + 1, $numAlign * 2 - 2) * 2;
        $result = array_fill(0, $numAlign, 0);
        $result[0] = 6;

        for ($i = $numAlign - 1, $pos = $this->size - 7; $i >= 1; $i--, $pos -= $step) {
            $result[$i] = $pos;
        }

        return $result;
    }

    private static function reedSolomonComputeDivisor(int $degree): array
    {
        $result = array_fill(0, $degree, 0);
        $result[$degree - 1] = 1;
        $root = 1;

        for ($i = 0; $i < $degree; $i++) {
            for ($j = 0; $j < $degree; $j++) {
                $result[$j] = self::reedSolomonMultiply($result[$j], $root);
                if ($j + 1 < $degree) {
                    $result[$j] ^= $result[$j + 1];
                }
            }
            $root = self::reedSolomonMultiply($root, 0x02);
        }

        return $result;
    }

    private static function reedSolomonComputeRemainder(array $data, array $divisor): array
    {
        $result = array_fill(0, count($divisor), 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $result[0];
            for ($i = 0; $i < count($result) - 1; $i++) {
                $result[$i] = $result[$i + 1] ^ self::reedSolomonMultiply($divisor[$i], $factor);
            }
            $result[count($result) - 1] = self::reedSolomonMultiply($divisor[count($divisor) - 1], $factor);
        }

        return $result;
    }

    private static function reedSolomonMultiply(int $x, int $y): int
    {
        $z = 0;
        for ($i = 7; $i >= 0; $i--) {
            $z = (($z << 1) ^ (($z >> 7) * 0x11D)) & 0xFF;
            if ((($y >> $i) & 1) !== 0) {
                $z ^= $x;
            }
        }

        return $z;
    }
}
