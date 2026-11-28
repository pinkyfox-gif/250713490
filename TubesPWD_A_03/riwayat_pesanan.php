
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = require __DIR__ . '/database.php';

$userId = $_SESSION['user_id'];

// Mengambil seluruh pesanan milik user yang sedang login
$query = $pdo->prepare(
    "SELECT *
     FROM orders
     WHERE user_id = :user_id
     ORDER BY created_at DESC"
);

$query->execute(array(
    ':user_id' => $userId
));

$pesanan = $query->fetchAll();

require __DIR__ . '/riwayat_pesanan.xhtml';
?>
