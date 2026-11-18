<?php
/**
 * BLOOM NAIL & BEAUTY
 * Database Helper - Singleton PDO connection
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $required = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
            foreach ($required as $constant) {
                if (!defined($constant)) {
                    error_log('Bloom database configuration missing constant: ' . $constant);
                    throw new RuntimeException('Cấu hình cơ sở dữ liệu chưa đầy đủ: thiếu hằng số ' . $constant . '.');
                }
            }

            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => true,
                PDO::ATTR_TIMEOUT => 10,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHARSET,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                error_log('Bloom database connection error: ' . $e->getMessage());
                http_response_code(500);
                throw new RuntimeException(
                    'Không thể kết nối cơ sở dữ liệu. Vui lòng kiểm tra MySQL trong XAMPP Control Panel đã được bật (Start) chưa hoặc cấu hình tại config/database.php. Chi tiết lỗi: ' . $e->getMessage()
                );
            }
        }

        return self::$instance;
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::getConnection()->inTransaction()) {
            return self::getConnection()->rollBack();
        }
        return false;
    }
}
