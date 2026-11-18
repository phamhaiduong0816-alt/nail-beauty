<?php
/**
 * BLOOM NAIL & BEAUTY
 * Session & CSRF Security Helper
 */
class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_strict_mode', '1');

        // Use this account-specific writable temp directory instead of the
        // host's inaccessible default /php_sessions directory.
        $tempRoot = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR);
        $sessionDir = $tempRoot . DIRECTORY_SEPARATOR . 'bloom_sessions_if0_43462000';

        if (!is_dir($sessionDir)) {
            @mkdir($sessionDir, 0700, true);
        }

        if (!is_dir($sessionDir) || !is_writable($sessionDir)) {
            error_log('Bloom session error: temporary session directory is not writable.');
            throw new RuntimeException('Không thể khởi tạo phiên làm việc. Vui lòng thử lại sau.');
        }

        if (ini_set('session.save_path', $sessionDir) === false) {
            error_log('Bloom session error: unable to set session.save_path.');
            throw new RuntimeException('Không thể cấu hình phiên làm việc trên máy chủ.');
        }

        if (!@session_start()) {
            error_log('Bloom session error: session_start() failed.');
            throw new RuntimeException('Không thể khởi tạo phiên làm việc. Vui lòng thử lại sau.');
        }
    }

    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    public static function setFlash(string $type, string $message): void
    {
        self::start();
        $_SESSION['flash'][$type] = $message;
    }

    public static function hasFlash(string $type): bool
    {
        self::start();
        return isset($_SESSION['flash'][$type]);
    }

    public static function getFlash(string $type): ?string
    {
        self::start();
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' .
            htmlspecialchars(self::csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validateCsrf(): bool
    {
        self::start();
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function login(array $userData, ?array $profileData = null): void
    {
        self::regenerate();
        $_SESSION['user'] = [
            'id' => $userData['id'],
            'username' => $userData['username'],
            'email' => $userData['email'],
            'role' => $userData['role'],
            'status' => $userData['status'],
        ];

        if ($userData['role'] === 'CUSTOMER' && $profileData) {
            $_SESSION['customer'] = [
                'id' => $profileData['id'],
                'full_name' => $profileData['full_name'],
                'phone' => $profileData['phone'],
                'address' => $profileData['address'] ?? '',
            ];
        } elseif ($userData['role'] === 'STAFF' && $profileData) {
            $_SESSION['staff'] = [
                'id' => $profileData['id'],
                'full_name' => $profileData['full_name'],
                'phone' => $profileData['phone'],
                'title' => $profileData['title'] ?? 'Kỹ thuật viên',
            ];
        }
    }

    public static function logout(): void
    {
        self::start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    public static function isLoggedIn(): bool
    {
        self::start();
        return !empty($_SESSION['user']['id']);
    }

    public static function user(): ?array
    {
        self::start();
        return $_SESSION['user'] ?? null;
    }

    public static function userId(): ?int
    {
        return self::user()['id'] ?? null;
    }

    public static function role(): ?string
    {
        return self::user()['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'ADMIN';
    }

    public static function isStaff(): bool
    {
        return self::role() === 'STAFF';
    }

    public static function isCustomer(): bool
    {
        return self::role() === 'CUSTOMER';
    }

    public static function customer(): ?array
    {
        self::start();
        return $_SESSION['customer'] ?? null;
    }

    public static function customerId(): ?int
    {
        return self::customer()['id'] ?? null;
    }

    public static function staff(): ?array
    {
        self::start();
        return $_SESSION['staff'] ?? null;
    }

    public static function staffId(): ?int
    {
        return self::staff()['id'] ?? null;
    }
}
