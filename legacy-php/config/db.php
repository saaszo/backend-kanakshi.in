<?php
/**
 * Database Connection (PDO)
 * Dropshipping Ecommerce Website
 * ------------------------------------------------
 * Uses PDO with prepared statements for security.
 */

// Load config from db_config.php if it exists (installer-managed)
$dbConfigPath = __DIR__ . '/db_config.php';
if (file_exists($dbConfigPath)) {
    $cfg = require $dbConfigPath;
    define('DB_HOST', $cfg['host'] ?? 'localhost');
    define('DB_PORT', $cfg['port'] ?? '3306');
    define('DB_NAME', $cfg['database'] ?? '');
    define('DB_USER', $cfg['username'] ?? '');
    define('DB_PASS', $cfg['password'] ?? '');
    define('DB_CHARSET', $cfg['charset'] ?? 'utf8mb4');
} else {
    define('DB_HOST', 'localhost'); // use localhost to trigger unix_socket check
    define('DB_PORT', '3306');
    define('DB_NAME', 'luxury_store');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
}

/**
 * Returns a singleton PDO instance.
 * Call getDB() anywhere you need the database.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        // XAMPP Mac Socket fix
        if (DB_HOST === 'localhost' && PHP_OS_FAMILY === 'Darwin' && file_exists('/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock')) {
            $dsn .= ';unix_socket=/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock';
        }

        // Handle PHP 8.5+ Pdo\Mysql deprecation gracefully
        $initCommand = defined('Pdo\Mysql::ATTR_INIT_COMMAND') 
            ? \Pdo\Mysql::ATTR_INIT_COMMAND 
            : (defined('PDO::MYSQL_ATTR_INIT_COMMAND') ? PDO::MYSQL_ATTR_INIT_COMMAND : 1002);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            $initCommand => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('[DB ERROR] ' . $e->getMessage());
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]));
        }
    }

    return $pdo;
}
