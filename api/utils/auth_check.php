<?php
/**
 * api/utils/auth_check.php
 *
 * Lightweight session-based authentication helpers.
 * Fully AJAX-safe and automatically normalizes $_SESSION['user'].
 */

require_once __DIR__ . '/response.php';

class Auth
{
    /**
     * Ensure session is started and normalize session keys.
     */
    public static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }

        // Auto-normalize user session keys
        if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
            $_SESSION['user_id']   = $_SESSION['user']['user_id'] ?? $_SESSION['user_id'] ?? null;
            $_SESSION['username']  = $_SESSION['user']['username'] ?? $_SESSION['username'] ?? null;
            $_SESSION['role']      = $_SESSION['user']['role'] ?? $_SESSION['role'] ?? null;
            $_SESSION['full_name'] = $_SESSION['user']['full_name'] ?? $_SESSION['full_name'] ?? null;
        }
    }

    /**
     * Require user to be logged in. Otherwise responds with 401.
     */
    public static function requireLogin(): void
    {
        self::ensureSession();
        if (empty($_SESSION['user_id'])) {
            ob_clean();
            Response::error('Authentication required', 401);
        }
    }

    /**
     * Require user to have a specific role (e.g. 'admin').
     *
     * @param string|array $roles
     */
    public static function requireRole($roles): void
    {
        self::ensureSession();
        $role = $_SESSION['role'] ?? null;

        if (is_array($roles)) {
            if (!in_array($role, $roles, true)) {
                ob_clean();
                Response::error('Insufficient privileges', 403);
            }
        } else {
            if ($role !== $roles) {
                ob_clean();
                Response::error('Insufficient privileges', 403);
            }
        }
    }

    /**
     * Return currently logged in user's minimal info or null.
     *
     * @return array|null
     */
    public static function currentUser(): ?array
    {
        self::ensureSession();
        if (empty($_SESSION['user_id'])) return null;

        return [
            'user_id'   => $_SESSION['user_id'],
            'username'  => $_SESSION['username'] ?? null,
            'role'      => $_SESSION['role'] ?? null,
            'full_name' => $_SESSION['full_name'] ?? null
        ];
    }

    /**
     * Logout helper (destroys session and cookie)
     */
    public static function logout(): void
    {
        self::ensureSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }
}

/**
 * Procedural wrappers for backward compatibility
 */
function isLoggedIn(): bool
{
    return Auth::currentUser() !== null;
}

function isAdmin(): bool
{
    $user = Auth::currentUser();
    return $user && ($user['role'] === 'admin');
}

/**
 * AJAX-safe auth_check helper
 *
 * @param array $roles
 */
function auth_check(array $roles = []): void
{
    ob_start(); // catch accidental output
    Auth::requireLogin();
    if (!empty($roles)) {
        Auth::requireRole($roles);
    }
    ob_end_clean(); // discard any accidental output
}
