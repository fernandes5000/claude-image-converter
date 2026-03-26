<?php

/**
 * ImageConverter
 *
 * Handles all server-side image conversion logic using PHP's GD extension.
 * Responsible for validating the uploaded file's MIME type, loading it into
 * a GD resource, compositing it onto a clean canvas (with proper transparency
 * handling), encoding the result in the requested format, and returning the
 * converted image as a base64-encoded payload ready for HTTP delivery.
 *
 * Supported input formats : JPEG, PNG, GIF, WebP, BMP, AVIF
 * Supported output formats : JPEG, PNG, GIF, WebP, BMP
 *
 * All error conditions are communicated via RuntimeException whose message
 * is an i18n translation key (e.g. 'error_format'), allowing the caller to
 * resolve the human-readable string in any supported language.
 */
class ImageConverter
{
    /**
     * MIME types accepted as input.
     * Any upload whose detected MIME is not in this list is rejected before
     * a single pixel is read, preventing unnecessary memory allocation.
     */
    private const ALLOWED_MIME = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/avif',
    ];

    /**
     * Maps normalised file extensions to their canonical MIME type.
     * Used to populate the 'mime' key of the result array so the caller
     * can set the correct Content-Type or data-URI prefix without guessing.
     */
    private const MIME_MAP = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'bmp'  => 'image/bmp',
    ];

    /**
     * Convert an uploaded image file to the specified format.
     *
     * Orchestrates the full conversion pipeline:
     *   1. Clamp quality to the valid 1–100 range.
     *   2. Detect and validate the input MIME type.
     *   3. Load the source image into a GD resource.
     *   4. Allocate a truecolor canvas with correct background/transparency.
     *   5. Copy the source pixels onto the canvas.
     *   6. Render the canvas to a binary string in the target format.
     *   7. Return metadata and base64-encoded image data to the caller.
     *
     * @param  array  $uploadedFile  Entry from $_FILES (keys: tmp_name, name, error, …).
     * @param  string $targetFormat  Desired output extension: 'jpg', 'png', 'webp', 'gif', 'bmp'.
     * @param  int    $quality       Compression quality from 1 (lowest) to 100 (highest).
     *                               For PNG this is inverted internally to a 0–9 compression level.
     *                               Ignored by GIF and BMP (lossless formats).
     *
     * @return array {
     *   name: string,   // Suggested filename including extension, e.g. "photo.webp"
     *   mime: string,   // MIME type of the output, e.g. "image/webp"
     *   size: int,      // Byte length of the raw converted image
     *   b64:  string,   // Base64-encoded image data (no data-URI prefix)
     *   w:    int,      // Image width in pixels
     *   h:    int       // Image height in pixels
     * }
     *
     * @throws RuntimeException 'error_format'  – Input MIME type is not supported.
     * @throws RuntimeException 'error_read'    – GD could not decode the source file.
     * @throws RuntimeException 'error_convert' – GD failed to encode the output format.
     */
    public function convert(array $uploadedFile, string $targetFormat, int $quality): array
    {
        $quality = max(1, min(100, $quality));
        $mime    = mime_content_type($uploadedFile['tmp_name']);

        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('error_format');
        }

        $src = $this->loadImage($uploadedFile['tmp_name'], $mime);

        if ($src === false) {
            throw new RuntimeException('error_read');
        }

        $w   = imagesx($src);
        $h   = imagesy($src);
        $out = $this->prepareCanvas($w, $h, $targetFormat);

        imagecopy($out, $src, 0, 0, 0, 0, $w, $h);
        imagedestroy($src);

        $imgData = $this->renderToString($out, $targetFormat, $quality);
        imagedestroy($out);

        if ($imgData === false || $imgData === '') {
            throw new RuntimeException('error_convert');
        }

        $ext      = $targetFormat === 'jpeg' ? 'jpg' : $targetFormat;
        $origName = pathinfo($uploadedFile['name'], PATHINFO_FILENAME);

        return [
            'name' => "{$origName}.{$ext}",
            'mime' => self::MIME_MAP[$ext] ?? 'image/png',
            'size' => strlen($imgData),
            'b64'  => base64_encode($imgData),
            'w'    => $w,
            'h'    => $h,
        ];
    }

    // ── Private helpers ────────────────────────────────────────────────────

    /**
     * Load an image file from disk into a GD resource.
     *
     * Selects the appropriate GD factory function based on the detected MIME
     * type. AVIF support depends on the PHP build; if imagecreatefromavif()
     * does not exist the method returns false, which the caller treats as a
     * read failure ('error_read').
     *
     * @param  string $path Absolute filesystem path to the uploaded temp file.
     * @param  string $mime Detected MIME type (must be a value in ALLOWED_MIME).
     *
     * @return resource|GdImage|false GD image resource on success, false on failure.
     */
    private function loadImage(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/gif'  => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/bmp'  => imagecreatefrombmp($path),
            'image/avif' => function_exists('imagecreatefromavif')
                                ? imagecreatefromavif($path)
                                : false,
            default      => false,
        };
    }

    /**
     * Allocate a truecolor canvas with the correct background for the target format.
     *
     * Formats that support an alpha channel (PNG, WebP, GIF) receive a fully
     * transparent background so that transparent areas in the source image are
     * preserved rather than filled with an opaque colour.
     *
     * Formats that do not support transparency (JPEG, BMP) receive a solid
     * white background, which is the conventional fallback when flattening
     * alpha compositing onto an opaque surface.
     *
     * @param  int    $w      Canvas width in pixels.
     * @param  int    $h      Canvas height in pixels.
     * @param  string $format Target format extension ('png', 'webp', 'gif', 'jpg', 'bmp', …).
     *
     * @return resource|GdImage Prepared GD truecolor canvas.
     */
    private function prepareCanvas(int $w, int $h, string $format)
    {
        $canvas = imagecreatetruecolor($w, $h);

        if (in_array($format, ['png', 'webp', 'gif'], true)) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefill($canvas, 0, 0, $transparent);
        } else {
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
        }

        return $canvas;
    }

    /**
     * Render a GD canvas to a raw binary string in the specified format.
     *
     * Uses output buffering to capture the output of GD's image*() functions,
     * which write directly to stdout when no file-path argument is provided.
     * PNG quality is derived by inverting the 1–100 quality scale to the 0–9
     * zlib compression level expected by imagepng().
     *
     * @param  resource|GdImage $canvas  The prepared GD canvas to encode.
     * @param  string           $format  Target format extension.
     * @param  int              $quality Quality/compression level (1–100).
     *
     * @return string|false Raw binary image data on success, false on failure.
     */
    private function renderToString($canvas, string $format, int $quality)
    {
        ob_start();

        $ok = match ($format) {
            'jpg', 'jpeg' => imagejpeg($canvas, null, $quality),
            'png'         => imagepng($canvas, null, (int) round(9 * (1 - $quality / 100))),
            'gif'         => imagegif($canvas),
            'webp'        => imagewebp($canvas, null, $quality),
            'bmp'         => imagebmp($canvas),
            default       => false,
        };

        $data = ob_get_clean();

        return ($ok && $data) ? $data : false;
    }

    /**
     * Format a byte count into a human-readable string with the appropriate unit.
     *
     * Thresholds:
     *   - <     1 024 bytes   →  "N B"
     *   - < 1 048 576 bytes   →  "N.n KB"
     *   - otherwise           →  "N.n MB"
     *
     * @param  int $bytes Raw byte count (must be non-negative).
     *
     * @return string Formatted string, e.g. "23.4 KB".
     */
    public static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024)      return "{$bytes} B";
        if ($bytes < 1_048_576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1_048_576, 1) . ' MB';
    }
}