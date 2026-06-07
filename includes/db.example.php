<?php
/*
 * Project: Organisation Voting System
 * PHP: 8.2.12 | Server: XAMPP Linux
 * Web root: /opt/lampp/htdocs/Voting_System_Project/
 * File: includes/db.php
 * Purpose: PDO database connection — singleton pattern
 *
 * Requirements (Copilot must follow all of these):
 *   - Use PDO only, never mysqli
 *   - DSN: mysql:host=localhost;dbname=voting_system;charset=utf8mb4
 *   - DB user: root | DB pass: (your xampp mysql root password)
 *   - PDO::ATTR_ERRMODE        => PDO::ERRMODE_EXCEPTION
 *   - PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
 *   - PDO::ATTR_EMULATE_PREPARES   => false
 *   - Singleton: if $pdo already exists, reuse it — don't reconnect
 *   - Wrap connection in try/catch, die with safe error on failure
 *   - Return: $pdo object ready to use
 */

$pdo = null;

if ($pdo === null) {
    $dsn = 'mysql:host=localhost;dbname=voting_system;charset=utf8mb4';
    $username = 'root';
    $password = 'YOUR_DB_PASSWORD';

    try {
        $pdo = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        die('Database connection error. Please try again later.');
    }
}

return $pdo;
