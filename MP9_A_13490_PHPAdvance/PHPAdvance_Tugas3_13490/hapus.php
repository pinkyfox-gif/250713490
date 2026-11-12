<?php
require 'koneksi.php';

$id = $_GET['id'] ?? '';

if (empty($id) || !ctype_digit($id)) {
    die('ID tidak valid!');
}

$sql = mysqli_prepare($conn, "DELETE FROM mahasiswa WHERE id = ?");

mysqli_stmt_bind_param($sql, "i", $id);

if (mysqli_stmt_execute($sql)) {
    if (mysqli_stmt_affected_rows($sql) > 0) {
        echo 'Data berhasil dihapus.';
    } else {
        echo 'Data tidak ditemukan.';
    }
} else {
    echo 'Gagal menghapus data.';
}

echo '<br><a href="index.php">Kembali</a>';

mysqli_stmt_close($sql);
mysqli_close($conn);

?>