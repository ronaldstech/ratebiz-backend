<?php
namespace App\Controllers;

use App\Helpers\Response;

class UploadController
{
    public function upload(): void
    {
        try {
            if (!isset($_FILES['file'])) {
                Response::json(['error' => 'No file uploaded'], 400);
            }

            $file = $_FILES['file'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                Response::json(['error' => 'File upload error: ' . $file['error']], 500);
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                Response::json(['error' => 'Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.'], 400);
            }

            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../../public/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_', true) . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Return public URL (adjust based on your server configuration)
                // Assuming the server root points to public/ or equivalent
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                $host = $_SERVER['HTTP_HOST'];
                // Adjust this path if your app is in a subdirectory
                $baseUrl = $protocol . $host . '/ratebiz/uploads/'; 
                
                Response::json(['url' => $baseUrl . $filename], 201);
            } else {
                Response::json(['error' => 'Failed to move uploaded file'], 500);
            }

        } catch (\Exception $e) {
            Response::json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }
}
