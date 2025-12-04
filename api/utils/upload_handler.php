<?php
/**
 * api/utils/upload_handler.php
 *
 * Robust file upload utility. Returns associative result with
 * - success: bool
 * - filename: string|null
 * - error: string|null
 *
 * Usage:
 *   require_once __DIR__ . '/validation.php';
 *   require_once __DIR__ . '/upload_handler.php';
 *
 *   $res = UploadHandler::handle($_FILES['project_file'], __DIR__ . '/../../uploads/project_files', $allowed_exts, 10 * 1024 * 1024);
 */

require_once __DIR__ . '/validation.php';

class UploadHandler
{
    /**
     * Handle uploaded file safely.
     *
     * @param array $file - element from $_FILES
     * @param string $destDir - absolute or relative path where file will be stored (ensure trailing slash not required)
     * @param array $allowedExtensions - lowercase extensions, e.g. ['pdf','docx']
     * @param int $maxBytes - maximum file size in bytes
     * @return array ['success'=>bool, 'filename'=>string|null, 'error'=>string|null]
     */
    public static function handle(array $file, string $destDir, array $allowedExtensions = [], int $maxBytes = 5242880): array
    {
        // Basic checks
        if (!isset($file) || !is_array($file) || empty($file['name'])) {
            return ['success' => false, 'filename' => null, 'error' => 'No file uploaded'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'filename' => null, 'error' => 'Invalid upload'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $err = self::codeToMessage($file['error']);
            return ['success' => false, 'filename' => null, 'error' => $err];
        }

        if ($file['size'] > $maxBytes) {
            return ['success' => false, 'filename' => null, 'error' => 'File too large'];
        }

        // Extract extension and validate
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedExtensions) && !in_array($ext, $allowedExtensions, true)) {
            return ['success' => false, 'filename' => null, 'error' => 'Invalid file type'];
        }

        // Validate MIME type with finfo
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        // Basic mapping allowlist (extend as needed)
        $allowedMimeByExt = [
            'pdf'  => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'doc'  => 'application/msword',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt'  => 'text/plain',
            'zip'  => 'application/zip',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp'
        ];
        if (!empty($allowedExtensions) && isset($allowedMimeByExt[$ext]) && stripos($mime, $allowedMimeByExt[$ext]) === false && !self::isImageExtension($ext)) {
            // allow images to pass more flexibly because some JPEG variants may report differently
            return ['success' => false, 'filename' => null, 'error' => 'File MIME type mismatch'];
        }

        // Prepare dest dir
        if (!Validation::ensureDirectory($destDir)) {
            return ['success' => false, 'filename' => null, 'error' => 'Unable to create destination directory'];
        }

        // sanitize filename
        try {
            $safeName = Validation::safeFilename($file['name']);
        } catch (Exception $e) {
            return ['success' => false, 'filename' => null, 'error' => 'Failed to generate filename'];
        }

        $destination = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'filename' => null, 'error' => 'Failed to move uploaded file'];
        }

        // Set restrictive permissions
        @chmod($destination, 0644);

        return ['success' => true, 'filename' => $safeName, 'error' => null];
    }

    /**
     * Helper to determine if extension is an image type
     *
     * @param string $ext
     * @return bool
     */
    private static function isImageExtension(string $ext): bool
    {
        return in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true);
    }

    /**
     * Convert PHP upload error code to message
     *
     * @param int $code
     * @return string
     */
    private static function codeToMessage(int $code): string
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'The uploaded file exceeds the permitted size';
            case UPLOAD_ERR_PARTIAL:
                return 'The uploaded file was only partially uploaded';
            case UPLOAD_ERR_NO_FILE:
                return 'No file was uploaded';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Missing a temporary folder';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Failed to write file to disk';
            case UPLOAD_ERR_EXTENSION:
                return 'File upload stopped by a PHP extension';
            default:
                return 'Unknown upload error';
        }
    }
}
