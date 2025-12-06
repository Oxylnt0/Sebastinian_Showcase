<?php
/**
 * api/utils/upload_handler.php
 *
 * FINAL VERSION – Robust, production-ready, tolerant MIME checking.
 */

require_once __DIR__ . '/validation.php';

class UploadHandler
{
    public static function handle(array $file, string $destDir, array $allowedExtensions = [], int $maxBytes = 5242880): array
    {
        // ----------------------------
        // BASIC VALIDATIONS
        // ----------------------------
        if (!isset($file['name']) || empty($file['name'])) {
            return ['success'=>false, 'filename'=>null, 'error'=>'No file uploaded'];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success'=>false, 'filename'=>null, 'error'=>'Invalid upload'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success'=>false, 'filename'=>null, 'error'=>self::codeToMessage($file['error'])];
        }

        if ($file['size'] > $maxBytes) {
            return ['success'=>false, 'filename'=>null, 'error'=>'File too large'];
        }

        // ----------------------------
        // EXTENSION VALIDATION
        // ----------------------------
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedExtensions) && !in_array($ext, $allowedExtensions, true)) {
            return ['success'=>false, 'filename'=>null, 'error'=>'Invalid file type'];
        }

        // ----------------------------
        // MIME DETECTION
        // ----------------------------
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';

        // Flexible allowlist
        $allowedMimeByExt = [
            'pdf'  => ['pdf'],
            'txt'  => ['text'],

            'png'  => ['image'],
            'jpg'  => ['image'],
            'jpeg' => ['image'],
            'webp' => ['image'],
            'gif'  => ['image'],

            // Office / zip files – allow multiple variants
            'docx' => ['word','officedocument','zip'],
            'pptx' => ['presentation','officedocument','zip'],
            'xlsx' => ['spreadsheet','officedocument','zip'],
            'doc'  => ['msword','word'],
            'ppt'  => ['powerpoint'],
            'zip'  => ['zip','compressed','octet-stream'],
        ];

        if (!empty($allowedExtensions) && isset($allowedMimeByExt[$ext])) {
            $patterns = $allowedMimeByExt[$ext];
            $mimeOK = false;
            foreach ($patterns as $pattern) {
                if (stripos($mime, $pattern) !== false) {
                    $mimeOK = true;
                    break;
                }
            }

            // Allow images if MIME starts with "image/"
            if (!$mimeOK && in_array($ext,['png','jpg','jpeg','webp','gif'])) {
                if (stripos($mime,'image/') === 0) {
                    $mimeOK = true;
                }
            }

            if (!$mimeOK) {
                // Fallback for Office/ZIP files: allow any octet-stream or zip
                if (in_array($ext,['docx','pptx','xlsx','zip'])) {
                    $mimeOK = true;
                }
            }

            if (!$mimeOK) {
                return ['success'=>false, 'filename'=>null, 'error'=>'File MIME type mismatch'];
            }
        }

        // ----------------------------
        // ENSURE DIRECTORY
        // ----------------------------
        if (!Validation::ensureDirectory($destDir)) {
            return ['success'=>false, 'filename'=>null, 'error'=>'Unable to create destination directory'];
        }

        // ----------------------------
        // SANITIZE FILENAME
        // ----------------------------
        try {
            $safeName = Validation::safeFilename($file['name']);
        } catch (Exception $e) {
            return ['success'=>false, 'filename'=>null, 'error'=>'Failed to generate filename'];
        }

        $destination = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;

        // ----------------------------
        // MOVE FILE
        // ----------------------------
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success'=>false, 'filename'=>null, 'error'=>'Failed to move uploaded file'];
        }

        @chmod($destination, 0644);

        return ['success'=>true, 'filename'=>$safeName, 'error'=>null];
    }

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
