<?php
namespace App\Controllers;

use App\Helpers\Response;

class UploadController
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp'
    ];

    public function upload(): void
    {
        try {
            if (empty($_FILES['file'])) {
                Response::json(['error' => 'No file uploaded'], 400);
                return;
            }

            $file = $_FILES['file'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                Response::json(['error' => 'Upload error code: ' . $file['error']], 400);
                return;
            }

            if ($file['size'] > self::MAX_FILE_SIZE) {
                Response::json(['error' => 'File size exceeds 5MB limit'], 400);
                return;
            }

            $mimeType = mime_content_type($file['tmp_name']);
            if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
                Response::json([
                    'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP'
                ], 400);
                return;
            }

            $uploadDir = realpath(__DIR__ . '/../../public') . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = self::ALLOWED_MIME_TYPES[$mimeType];
            $filename  = bin2hex(random_bytes(16)) . '.' . $extension;
            $target    = $uploadDir . $filename;

            $this->reencodeImage($file['tmp_name'], $mimeType, $target);

            $url = $this->publicUrl('uploads/' . $filename);

            Response::json([
                'success' => true,
                'url'     => $url
            ], 201);

        } catch (\Throwable $e) {
            Response::json([
                'error' => 'Upload failed',
                'debug' => getenv('APP_ENV') === 'dev' ? $e->getMessage() : null
            ], 500);
        }
    }

    private function reencodeImage(string $source, string $mime, string $target): void
    {
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg(imagecreatefromjpeg($source), $target, 90);
                break;

            case 'image/png':
                imagepng(imagecreatefrompng($source), $target, 8);
                break;

            case 'image/gif':
                imagegif(imagecreatefromgif($source), $target);
                break;

            case 'image/webp':
                imagewebp(imagecreatefromwebp($source), $target, 90);
                break;

            default:
                throw new \RuntimeException('Unsupported image format');
        }
    }

    private function publicUrl(string $path): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'];

        return sprintf('%s://%s/ratebiz/%s', $scheme, $host, ltrim($path, '/'));
    }
}
