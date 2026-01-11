<?php
// ----------------------------
// SQLite Database Helper
// ----------------------------
function db()
{
    static $pdo;
    if (!$pdo) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../database/db.sqlite');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $pdo;
}

/**
 * Helper for executing queries
 * Supports optional parameters
 */
function db_query(string $sql, array $params = [])
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}


/**
 * Initialize DB
 */
function init_db()
{
    // Ensure folder exists
    if (!file_exists(__DIR__ . '/../database')) {
        mkdir(__DIR__ . '/../database', 0777, true);
    }
    // Create empty SQLite file if not exists
    if (!file_exists(__DIR__ . '/../database/db.sqlite')) {
        file_put_contents(__DIR__ . '/../database/db.sqlite', '');
    }

}

// Automatically initialize DB when included
init_db();
