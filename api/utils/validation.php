<?php
/**
 * api/utils/validation.php
 *
 * General validation and filename utilities.
 * Usage:
 *   require_once __DIR__ . '/validation.php';
 *   $safe = Validation::sanitizeText($_POST['title']);
 */

class Validation
{
    /**
     * Trim and strip control characters, limit length optionally.
     *
     * @param mixed $value
     * @param int|null $maxLength
     * @return string
     */
    public static function sanitizeText($value, ?int $maxLength = null): string
    {
        $s = (string) $value;
        // Remove ASCII control chars except newline/tab
        $s = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', $s);
        $s = trim($s);
        if ($maxLength !== null) {
            $s = mb_substr($s, 0, (int)$maxLength);
        }
        return $s;
    }

    /**
     * Validate email
     *
     * @param string $email
     * @return bool
     */
    public static function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Validate required presence (after trimming)
     *
     * @param mixed $value
     * @return bool
     */
    public static function required($value): bool
    {
        if (is_array($value)) return !empty($value);
        return (trim((string)$value) !== '');
    }

    /**
     * Validate that value is one of allowed enumerations
     *
     * @param string $value
     * @param array $allowed
     * @return bool
     */
    public static function inEnum(string $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    /**
     * Create a safe, unique filename based on the original name.
     * Uses random bytes for uniqueness and strips dangerous chars.
     *
     * @param string $original
     * @return string
     * @throws Exception
     */
    public static function safeFilename(string $original): string
    {
        $original = basename($original);
        // allow letters, numbers, dots, underscores, hyphens
        $clean = preg_replace('/[^A-Za-z0-9._-]/', '_', $original);
        if ($clean === '' || $clean === '.' || $clean === '..') {
            $clean = 'file';
        }
        $random = bin2hex(random_bytes(8));
        $ts = time();
        return $ts . '_' . $random . '_' . $clean;
    }

    /**
     * Ensure a directory exists and is writable; create it when necessary.
     *
     * @param string $path
     * @param int $mode
     * @return bool
     */
    public static function ensureDirectory(string $path, int $mode = 0755): bool
    {
        if (is_dir($path)) {
            return is_writable($path);
        }
        // attempt recursive create
        return mkdir($path, $mode, true);
    }
}
