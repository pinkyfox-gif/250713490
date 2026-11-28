
<?php
session_start();

header('Content-Type: application/json; charset=utf-8');

// Memastikan request menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Metode tidak diizinkan.'
    ));
    exit;
}

// Memeriksa token CSRF
$csrfToken = $_POST['csrf_token'] ?? '';

if (
    !isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $csrfToken)
) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Permintaan tidak valid.'
    ));
    exit;
}

// Mengambil data dari JavaScript
$type = $_POST['type'] ?? '';
$value = trim($_POST['value'] ?? '');

// Memeriksa apakah input kosong
if ($value === '') {
    echo json_encode(array(
        'success' => true,
        'available' => false,
        'message' => 'Kolom wajib diisi.'
    ));
    exit;
}

try {
    $pdo = require __DIR__ . '/database.php';

    // Pemeriksaan username
    if ($type === 'username') {

        if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $value)) {
            echo json_encode(array(
                'success' => true,
                'available' => false,
                'message' => 'Username harus 4–30 karakter dan hanya berisi huruf, angka, atau garis bawah.'
            ));
            exit;
        }

        // Memeriksa username pada tabel users
        $checkUser = $pdo->prepare(
            'SELECT id FROM users
             WHERE username = :username
             LIMIT 1'
        );

        $checkUser->execute(array(
            ':username' => $value
        ));

        $userExists = $checkUser->fetch() !== false;

        // Memeriksa username pada tabel admins
        $checkAdmin = $pdo->prepare(
            'SELECT id FROM admins
             WHERE username = :username
             LIMIT 1'
        );

        $checkAdmin->execute(array(
            ':username' => $value
        ));

        $adminExists = $checkAdmin->fetch() !== false;

        $exists = $userExists || $adminExists;

        echo json_encode(array(
            'success' => true,
            'available' => !$exists,
            'message' => $exists
                ? 'Username sudah digunakan.'
                : 'Username tersedia.'
        ));
        exit;
    }

    // Pemeriksaan email
    elseif ($type === 'email') {

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(array(
                'success' => true,
                'available' => false,
                'message' => 'Format email tidak valid.'
            ));
            exit;
        }

        $checkEmail = $pdo->prepare(
            'SELECT id FROM users
             WHERE email = :email
             LIMIT 1'
        );

        $checkEmail->execute(array(
            ':email' => $value
        ));

        $exists = $checkEmail->fetch() !== false;

        echo json_encode(array(
            'success' => true,
            'available' => !$exists,
            'message' => $exists
                ? 'Email sudah terdaftar.'
                : 'Email tersedia.'
        ));
        exit;
    }

    // Jenis pemeriksaan tidak dikenal
    else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Jenis pemeriksaan tidak valid.'
        ));
        exit;
    }

} catch (PDOException $exception) {

    echo json_encode(array(
        'success' => false,
        'message' => 'Terjadi kesalahan saat memeriksa data.'
    ));
    exit;
}
?>
