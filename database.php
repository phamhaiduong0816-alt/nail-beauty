<?php
/**
 * NAIL & BEAUTY MANAGEMENT SYSTEM
 * File cấu hình cơ sở dữ liệu PDO & ứng dụng
 * Tự động chuyển đổi giữa môi trường XAMPP cục bộ và môi trường Hosting trực tuyến
 */

// Tự động nhận diện môi trường: Localhost (XAMPP máy cá nhân) hay Hosting Online
$hostName = $_SERVER['HTTP_HOST'] ?? '';
$isLocalhost = empty($hostName) 
    || in_array($hostName, ['localhost', '127.0.0.1']) 
    || str_starts_with($hostName, 'localhost:')
    || str_starts_with($hostName, '127.0.0.1:')
    || php_sapi_name() === 'cli';

if ($isLocalhost) {
    // 1. Cấu hình khi chạy trên máy tính cá nhân (XAMPP Windows mặc định)
    defined('DB_HOST')    or define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    defined('DB_PORT')    or define('DB_PORT', getenv('DB_PORT') ?: '3306');
    defined('DB_NAME')    or define('DB_NAME', getenv('DB_NAME') ?: 'nail_beauty');
    defined('DB_USER')    or define('DB_USER', getenv('DB_USER') ?: 'root');
    defined('DB_PASS')    or define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
    defined('DB_CHARSET') or define('DB_CHARSET', 'utf8mb4');
} else {
    // 2. Cấu hình khi upload lên Hosting trực tuyến (InfinityFree)
    defined('DB_HOST')    or define('DB_HOST', getenv('DB_HOST') ?: 'sql202.infinityfree.com');
    defined('DB_PORT')    or define('DB_PORT', getenv('DB_PORT') ?: '3306');
    defined('DB_NAME')    or define('DB_NAME', getenv('DB_NAME') ?: 'if0_43462000_if0_43462000_bloomnail');
    defined('DB_USER')    or define('DB_USER', getenv('DB_USER') ?: 'if0_43462000');
    defined('DB_PASS')    or define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'eGsu7af1zPKMz6');
    defined('DB_CHARSET') or define('DB_CHARSET', 'utf8mb4');
}

// Cấu hình đường dẫn hệ thống
defined('ROOT_PATH') or define('ROOT_PATH', dirname(__DIR__));
defined('APP_PATH') or define('APP_PATH', ROOT_PATH . '/app');
defined('PUBLIC_PATH') or define('PUBLIC_PATH', ROOT_PATH . '/public');

// Cấu hình Base URL tự động nhận diện theo thư mục chạy trong XAMPP htdocs hoặc CLI server
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Lấy thư mục gốc tương đối từ SCRIPT_NAME
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // Nếu chạy qua public/index.php thì loại bỏ đuôi /public
    $baseFolder = rtrim(preg_replace('#/public$#', '', $scriptDir), '/');

    define('BASE_URL', $protocol . $host . $baseFolder);
}

// Cấu hình thông tin thương hiệu tiệm Nail & Beauty
defined('APP_NAME') or define('APP_NAME', 'Bloom Nail & Beauty');
defined('APP_SLOGAN') or define('APP_SLOGAN', 'Nâng niu nét ngọc - Tỏa sáng vẻ đẹp tự nhiên');
defined('APP_PHONE') or define('APP_PHONE', '0708858686');
defined('APP_EMAIL') or define('APP_EMAIL', 'contact@bloomnail.vn');
defined('APP_ADDRESS') or define('APP_ADDRESS', 'Số 102 Đường Võ Văn Đặng, Phường Ngũ Hành Sơn, TP. Đà Nẵng');
defined('APP_TIME_OPEN') or define('APP_TIME_OPEN', '08:30 - 20:00 (Hàng ngày)');
