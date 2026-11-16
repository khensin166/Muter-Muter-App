<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FileSecurityService
{
    /**
     * Maximum allowed pixel width and height to prevent Decompression Bomb / OOM attacks.
     */
    private const MAX_IMAGE_DIMENSION = 4096;

    /**
     * Default maximum file size in bytes (5 MB).
     */
    private const DEFAULT_MAX_BYTES = 5 * 1024 * 1024;

    /**
     * Dangerous file extensions that must never appear in any segment of the filename.
     */
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8', 'phps', 'inc',
        'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'zsh', 'exe', 'bat', 'cmd', 'com', 'dll',
        'msi', 'vbs', 'js', 'mjs', 'jsp', 'jspx', 'asp', 'aspx', 'cer', 'asa', 'swf',
        'svg', 'svgz', 'html', 'htm', 'xhtml', 'shtml', 'htaccess', 'htpasswd', 'ini', 'env',
    ];

    /**
     * Malicious binary/text signatures that indicate embedded webshells, polyglots, or XSS scripts.
     */
    private const MALICIOUS_SIGNATURES = [
        '<?php',
        '<?=',
        '<script',
        '<svg',
        '__halt_compiler',
        'eval(',
        'system(',
        'shell_exec(',
        'passthru(',
        'proc_open(',
        'popen(',
        'pcntl_exec(',
        'base64_decode(',
        'assert(',
    ];

    /**
     * Validate, sanitize (re-encode images to strip EXIF/payloads), and store an uploaded file securely.
     *
     * @throws ValidationException
     */
    public static function validateAndStore(
        UploadedFile $file,
        string $directory,
        string $fieldName = 'file',
        bool $allowPdf = false,
        ?string $disk = null,
        int $maxSizeBytes = self::DEFAULT_MAX_BYTES
    ): string {
        $disk = $disk ?? (config('filesystems.default') === 'r2' || (env('CLOUDFLARE_R2_ACCESS_KEY_ID') && !app()->environment('testing')) ? 'r2' : 'public');
        // 1. Validate upload integrity & file size
        if (! $file->isValid()) {
            throw ValidationException::withMessages([
                $fieldName => 'Unggahan file gagal atau korup.',
            ]);
        }

        $fileSize = $file->getSize();
        if ($fileSize === false || $fileSize <= 0 || $fileSize > $maxSizeBytes) {
            throw ValidationException::withMessages([
                $fieldName => 'Ukuran file tidak valid atau melebihi batas maksimal 5 MB.',
            ]);
        }

        // 2. Inspect client filename for null-bytes & dangerous double extensions (e.g. shell.php.jpg)
        $originalName = $file->getClientOriginalName();
        if (str_contains($originalName, "\0") || str_contains($originalName, '/') || str_contains($originalName, '\\')) {
            self::rejectWithSecurityLog($fieldName, 'Null-byte atau karakter path traversal terdeteksi pada nama file.', [
                'original_name' => $originalName,
            ]);
        }

        $nameSegments = explode('.', strtolower($originalName));
        if (count($nameSegments) > 1) {
            // Check every segment after the base filename
            array_shift($nameSegments);
            foreach ($nameSegments as $segment) {
                $cleanSegment = trim($segment);
                if (in_array($cleanSegment, self::FORBIDDEN_EXTENSIONS, true)) {
                    self::rejectWithSecurityLog($fieldName, 'File ditolak karena memiliki ekstensi berbahaya atau dilarang (.' . $cleanSegment . ').', [
                        'original_name' => $originalName,
                        'blocked_extension' => $cleanSegment,
                    ]);
                }
            }
        }

        // 3. Detect real MIME type via Magic Bytes (finfo) — never trust client-provided Content-Type
        $realPath = $file->getRealPath() ?: $file->getPathname();
        if (! $realPath || ! is_file($realPath)) {
            throw ValidationException::withMessages([
                $fieldName => 'File sementara tidak ditemukan untuk inspeksi keamanan.',
            ]);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo ? finfo_file($finfo, $realPath) : false;

        $allowedMimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if ($allowPdf) {
            $allowedMimeMap['application/pdf'] = 'pdf';
        }

        if (! is_string($detectedMime) || ! isset($allowedMimeMap[$detectedMime])) {
            self::rejectWithSecurityLog(
                $fieldName,
                'Format isi file tidak diizinkan (' . ($detectedMime ?: 'unknown') . '). Gunakan file ' . ($allowPdf ? 'JPG, PNG, WebP, atau PDF' : 'gambar JPG, PNG, atau WebP') . ' murni.',
                [
                    'original_name' => $originalName,
                    'detected_mime' => $detectedMime,
                    'client_mime' => $file->getClientMimeType(),
                ]
            );
        }

        // 4. Deep Binary Scan for embedded PHP webshells, scripts, or polyglot payloads
        $rawContent = file_get_contents($realPath);
        if ($rawContent === false || $rawContent === '') {
            throw ValidationException::withMessages([
                $fieldName => 'Gagal membaca isi biner file.',
            ]);
        }

        $lowerContent = strtolower($rawContent);
        foreach (self::MALICIOUS_SIGNATURES as $signature) {
            if (str_contains($lowerContent, $signature)) {
                self::rejectWithSecurityLog(
                    $fieldName,
                    'File ditolak oleh sistem keamanan karena terdeteksi mengandung susupan kode/script berbahaya.',
                    [
                        'original_name' => $originalName,
                        'detected_mime' => $detectedMime,
                        'matched_signature' => $signature,
                    ]
                );
            }
        }

        $canonicalExt = $allowedMimeMap[$detectedMime];

        // 5. Handle PDF verification & storage
        if ($detectedMime === 'application/pdf') {
            if (! str_starts_with($rawContent, '%PDF-')) {
                self::rejectWithSecurityLog($fieldName, 'Header biner PDF tidak valid.', [
                    'original_name' => $originalName,
                ]);
            }

            // Block PDF files with embedded JavaScript or Launch actions
            if (str_contains($lowerContent, '/javascript') || str_contains($lowerContent, '/js ') || str_contains($lowerContent, '/js(') || str_contains($lowerContent, '/launch')) {
                self::rejectWithSecurityLog($fieldName, 'Dokumen PDF ditolak karena mengandung script aktif (/JavaScript atau /Launch).', [
                    'original_name' => $originalName,
                ]);
            }

            $relativePath = trim($directory, '/') . '/' . Str::uuid()->toString() . '.pdf';
            Storage::disk($disk)->put($relativePath, $rawContent);

            return $relativePath;
        }

        // 6. Handle Raster Image verification, Dimension bounds, and GD Re-encoding (EXIF & payload stripping)
        $imageInfo = @getimagesizefromstring($rawContent);
        if ($imageInfo === false || ($imageInfo[0] ?? 0) <= 0 || ($imageInfo[1] ?? 0) <= 0) {
            self::rejectWithSecurityLog($fieldName, 'Struktur biner gambar rusak atau bukan gambar valid.', [
                'original_name' => $originalName,
                'detected_mime' => $detectedMime,
            ]);
        }

        [$width, $height] = [$imageInfo[0], $imageInfo[1]];
        if ($width > self::MAX_IMAGE_DIMENSION || $height > self::MAX_IMAGE_DIMENSION) {
            throw ValidationException::withMessages([
                $fieldName => "Resolusi dimensi gambar ({$width}x{$height} piksel) melebihi batas aman maksimal " . self::MAX_IMAGE_DIMENSION . 'x' . self::MAX_IMAGE_DIMENSION . ' piksel.',
            ]);
        }

        $cleanImageBinary = self::reencodeCleanImage($rawContent, $detectedMime, $canonicalExt, $fieldName);

        // 7. Store using cryptographic UUID v4 filename (never preserve original user filename)
        $relativePath = trim($directory, '/') . '/' . Str::uuid()->toString() . '.' . $canonicalExt;

        if ($disk === 'r2') {
            try {
                Storage::disk('r2')->put($relativePath, $cleanImageBinary);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('R2 upload failed, falling back to local public disk: ' . $e->getMessage());
                $disk = 'public';
            }

            // Also mirror to local public disk so images load seamlessly in local development
            try {
                Storage::disk('public')->put($relativePath, $cleanImageBinary);
            } catch (\Throwable $e) {
                // ignore local mirror failure
            }
        } else {
            Storage::disk($disk)->put($relativePath, $cleanImageBinary);
        }

        return $relativePath;
    }

    /**
     * Re-encode image pixel data via PHP GD to strip 100% of EXIF metadata, comments, and hidden polyglot bytes.
     *
     * @throws ValidationException
     */
    private static function reencodeCleanImage(string $rawContent, string $mime, string &$extension, string $fieldName): string
    {
        if (! function_exists('imagecreatefromstring')) {
            return $rawContent;
        }

        $gdImage = @imagecreatefromstring($rawContent);
        if ($gdImage === false) {
            self::rejectWithSecurityLog($fieldName, 'Gagal memverifikasi matriks piksel gambar. Pastikan file adalah gambar murni.', [
                'detected_mime' => $mime,
            ]);
        }

        ob_start();
        $success = false;

        if ($mime === 'image/jpeg') {
            $success = @imagejpeg($gdImage, null, 88);
            $extension = 'jpg';
        } elseif ($mime === 'image/png') {
            @imagealphablending($gdImage, false);
            @imagesavealpha($gdImage, true);
            $success = @imagepng($gdImage, null, 6);
            $extension = 'png';
        } elseif ($mime === 'image/webp') {
            if (function_exists('imagewebp')) {
                $success = @imagewebp($gdImage, null, 85);
                $extension = 'webp';
            } else {
                $success = @imagejpeg($gdImage, null, 88);
                $extension = 'jpg';
            }
        }

        $cleanBinary = ob_get_clean();

        if (! $success || ! is_string($cleanBinary) || $cleanBinary === '') {
            throw ValidationException::withMessages([
                $fieldName => 'Gagal memproses sanitasi gambar.',
            ]);
        }

        return $cleanBinary;
    }

    /**
     * Log security threat event and throw ValidationException.
     *
     * @throws ValidationException
     */
    private static function rejectWithSecurityLog(string $fieldName, string $userMessage, array $context = []): never
    {
        AppLogger::warning('security', 'security.file_upload.rejected', $userMessage, array_merge($context, [
            'field' => $fieldName,
        ]));

        throw ValidationException::withMessages([
            $fieldName => $userMessage,
        ]);
    }

    /**
     * Resolve public CDN / storage URL for an uploaded media file.
     */
    public static function publicUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim(str_replace('/storage/', '', $path), '/');

        // If custom R2 public domain (r2.dev or custom CDN domain) is set and public
        $r2Url = env('CLOUDFLARE_R2_URL');
        if (!empty($r2Url) && !str_contains($r2Url, '.r2.cloudflarestorage.com')) {
            return rtrim($r2Url, '/') . '/' . $cleanPath;
        }

        return asset('storage/' . $cleanPath);
    }

    /**
     * Generate secure, temporary presigned URL for sensitive documents (e.g. KYC, payment proofs).
     */
    public static function temporaryUrl(?string $path, int $minutes = 15): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $cleanPath = ltrim(str_replace('/storage/', '', $path), '/');

        if (config('filesystems.disks.r2.key')) {
            try {
                return Storage::disk('r2')->temporaryUrl($cleanPath, now()->addMinutes($minutes));
            } catch (\Throwable $e) {
                return Storage::disk('r2')->url($cleanPath);
            }
        }

        return '/storage/' . $cleanPath;
    }

    /**
     * Safely delete an uploaded file from Cloudflare R2 and local public storage.
     */
    public static function deleteFile(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        // Handle external or full URLs
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsedUrl = parse_url($path);
            $host = $parsedUrl['host'] ?? '';
            $urlPath = $parsedUrl['path'] ?? '';

            $r2Url = env('CLOUDFLARE_R2_URL');
            $r2Host = !empty($r2Url) ? parse_url($r2Url, PHP_URL_HOST) : null;

            // Only delete if the URL points to our R2 public domain, S3 endpoint, or local dev server
            $isManagedStorage = ($r2Host && str_contains($host, $r2Host))
                || str_contains($host, '.r2.cloudflarestorage.com')
                || str_contains($host, 'localhost')
                || str_contains($host, '127.0.0.1');

            if (! $isManagedStorage) {
                return false;
            }

            $cleanPath = ltrim($urlPath, '/');
            $bucketName = env('CLOUDFLARE_R2_BUCKET', 'muter-muter-media');
            if (str_starts_with($cleanPath, $bucketName . '/')) {
                $cleanPath = substr($cleanPath, strlen($bucketName) + 1);
            }
        } else {
            $cleanPath = ltrim(str_replace('/storage/', '', $path), '/');
        }

        $cleanPath = preg_replace('#^/?(storage/)?#', '', $cleanPath);

        if (empty($cleanPath)) {
            return false;
        }

        $deleted = false;

        // 1. Delete from Cloudflare R2 if configured and not in test environment
        if (config('filesystems.disks.r2.key') && ! app()->environment('testing')) {
            try {
                if (Storage::disk('r2')->exists($cleanPath)) {
                    $deleted = Storage::disk('r2')->delete($cleanPath) || $deleted;
                    \Illuminate\Support\Facades\Log::info("File deleted from Cloudflare R2: {$cleanPath}");
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal menghapus file dari Cloudflare R2: {$cleanPath} - " . $e->getMessage());
            }
        }

        // 2. Delete from local public disk
        try {
            if (Storage::disk('public')->exists($cleanPath)) {
                $deleted = Storage::disk('public')->delete($cleanPath) || $deleted;
                \Illuminate\Support\Facades\Log::info("File deleted from local public storage: {$cleanPath}");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Gagal menghapus file dari disk lokal: {$cleanPath} - " . $e->getMessage());
        }

        return $deleted;
    }
}
