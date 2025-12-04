<?php
/**
 * api/utils/response.php
 *
 * Small response helper for JSON APIs.
 * Usage:
 *   require_once __DIR__ . '/response.php';
 *
 * Class usage:
 *   Response::success(['id'=>1], 'Created', 201);
 *   Response::error('Invalid input', 400);
 *
 * Procedural helpers:
 *   json_success(['id'=>1], 'Created', 201);
 *   json_error('Invalid input', 400);
 */

class Response
{
    /**
     * Send a JSON response and exit.
     *
     * @param array|string $payload
     * @param int $httpCode
     */
    public static function json($payload, int $httpCode = 200): void
    {
        if (!headers_sent()) {
            http_response_code($httpCode);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Content-Type-Options: nosniff');
        }
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Convenience success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $httpCode
     */
    public static function success($data = [], string $message = 'OK', int $httpCode = 200): void
    {
        $payload = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
        self::json($payload, $httpCode);
    }

    /**
     * Convenience error response.
     *
     * @param string $message
     * @param int $httpCode
     * @param array $meta
     */
    public static function error(string $message = 'Error', int $httpCode = 400, array $meta = []): void
    {
        $payload = [
            'status' => 'error',
            'message' => $message
        ];
        if (!empty($meta)) {
            $payload['meta'] = $meta;
        }
        self::json($payload, $httpCode);
    }
}

/**
 * Procedural helper functions for backward compatibility.
 */
function json_success($data = [], $message = 'OK', $httpCode = 200): void
{
    Response::success($data, $message, $httpCode);
}

function json_error($message = 'Error', $httpCode = 400, $meta = []): void
{
    Response::error($message, $httpCode, $meta);
}
