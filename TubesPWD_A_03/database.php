<?php
$host = 'localhost';
$dbname = 'bakery_db';
$dbuser = 'root';
$dbpass = '';

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false
);

return new PDO($dsn, $dbuser, $dbpass, $options);
?>