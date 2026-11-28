
<?php
session_start();

// Hapus session admin
unset(
    $_SESSION['admin_id'],
    $_SESSION['admin_username']
);

// Hapus seluruh session
$_SESSION = array();

session_destroy();

// Kembali ke halaman beranda
header('Location: index.php');
exit;
?>
