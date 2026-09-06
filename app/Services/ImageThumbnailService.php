<?php

namespace App\Services;

class ImageThumbnailService
{
    private string $cachePath;

    public function __construct(?string $cachePath = null)
    {
        $this->cachePath = $cachePath ?: (WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'thumbnails');
        if (! is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * Get or create a thumbnail from a source image path.
     *
     * @param string $sourcePath Absolute path to the original image file.
     * @param int $width Desired width in pixels.
     * @param int $height Desired height in pixels.
     * @param string $fit Fit strategy ('center', 'top-left', etc.)
     * @param int $quality Quality from 1-100.
     * @return string|null Absolute path to the thumbnail file, or fallback to original/null.
     */
    public function getThumbnail(
        string $sourcePath,
        int $width = 64,
        int $height = 64,
        string $fit = 'center',
        int $quality = 85
    ): ?string {
        if ($sourcePath === '' || ! file_exists($sourcePath) || ! is_readable($sourcePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        // SVG files are vector and cannot be processed by GD, return original directly
        if ($ext === 'svg') {
            return $sourcePath;
        }

        // Normalize dimensions
        $width = max(16, min(1000, $width));
        $height = max(16, min(1000, $height));

        $targetExt = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) ? $ext : 'png';
        if ($targetExt === 'jpg') {
            $targetExt = 'jpeg';
        }

        $mtime = filemtime($sourcePath) ?: 0;
        $hash = md5($sourcePath . '_' . $mtime . "_{$width}x{$height}_{$fit}_{$quality}");
        $cacheFile = $this->cachePath . DIRECTORY_SEPARATOR . $hash . '.' . $targetExt;

        if (file_exists($cacheFile)) {
            return $cacheFile;
        }

        try {
            \Config\Services::image()
                ->withFile($sourcePath)
                ->fit($width, $height, $fit)
                ->save($cacheFile, $quality);

            if (file_exists($cacheFile)) {
                return $cacheFile;
            }
        } catch (\Throwable $th) {
            log_message('error', 'Thumbnail generation failed for ' . $sourcePath . ': ' . $th->getMessage());
        }

        return $sourcePath;
    }
}
