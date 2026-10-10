<?php

/**
 * Database connection (XAMPP defaults).
 * Import setup.sql first to create the "login_system" database and users.
 */

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "login_system";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    exit(
        "Database connection failed. Please make sure MySQL is running " .
        "and that you imported <strong>setup.sql</strong>."
    );
}
