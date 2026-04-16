<?php
function get_db() {
    static $db = null;
    if ($db === null) {
        $dsn = 'mysql:host=localhost;dbname=laundry_db;charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $db = new PDO($dsn, 'root', '', $options);
    }
    return $db;
}
?>