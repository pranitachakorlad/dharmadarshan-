<?php
/**
 * Database configuration for XAMPP / shared hosting.
 * Update these values when deploying to production.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'HOSTINGER_DATABASE_NAME');
define('DB_USER', 'HOSTINGER_DATABASE_USERNAME');
define('DB_PASS', 'HOSTINGER_DATABASE_PASSWORD');
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'Dharma Darshan');
define('SITE_SLOGAN', 'A legacy for the new generation');
define('SITE_URL', 'https://dharmadarshan.in'); // e.g. http://localhost/dharmadarshan.in

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        ensureAppSchema($pdo);
    }
    return $pdo;
}

function ensureAppSchema(PDO $pdo): void
{

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            contact_no VARCHAR(20) NULL,
            email VARCHAR(150) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            birth_date DATE NULL,
            birth_time TIME NULL,
            birth_place VARCHAR(200) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    foreach (array_filter(array_map('trim', explode(';', file_get_contents(__DIR__ . '/../database/orders.sql')))) as $statement) { $pdo->exec($statement); }

    ensureColumn($pdo, 'users', 'contact_no', 'VARCHAR(20) NULL');
    ensureColumn($pdo, 'users', 'birth_date', 'DATE NULL');
    ensureColumn($pdo, 'users', 'birth_time', 'TIME NULL');
    ensureColumn($pdo, 'users', 'birth_place', 'VARCHAR(200) NULL');
    ensureIndex($pdo, 'users', 'idx_users_contact_no', 'contact_no');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            excerpt TEXT,
            content TEXT,
            image VARCHAR(255) DEFAULT 'assets/images/placeholder-blog.svg',
            is_published TINYINT(1) DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");


}

function ensureColumn(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$table, $column]);

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec("ALTER TABLE `$table` ADD `$column` $definition");
    }
}

function ensureIndex(PDO $pdo, string $table, string $index, string $column): void
{
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND INDEX_NAME = ?
    ");
    $stmt->execute([$table, $index]);

    if ((int) $stmt->fetchColumn() === 0) {
        try {
            $pdo->exec("CREATE INDEX `$index` ON `$table` (`$column`)");
        } catch (PDOException $e) {
            // Keep the app running even if old data prevents the index.
        }
    }
}
