
<?php
session_start();

function escape_html($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = array();
$success = false;
$databaseError = false;
$fullName = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Permintaan tidak valid. Silakan muat ulang halaman.';
    }

    if ($fullName === '') {
        $errors[] = 'Nama lengkap wajib diisi.';
    } elseif (strlen($fullName) > 100) {
        $errors[] = 'Nama lengkap maksimal 100 karakter.';
    }

    if (!preg_match('/^[A-Za-z0-9_]{4,30}$/', $username)) {
        $errors[] = 'Username harus 4–30 karakter dan hanya berisi huruf, angka, atau garis bawah.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } elseif (strlen($email) > 100) {
        $errors[] = 'Email maksimal 100 karakter.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (empty($errors)) {
        try {
            $pdo = require __DIR__ . '/database.php';

            $checkUsername = $pdo->prepare(
                'SELECT id FROM users WHERE username = :username LIMIT 1'
            );
            $checkUsername->execute(array(':username' => $username));

            if ($checkUsername->fetch()) {
                $errors[] = 'Username sudah digunakan.';
            }

            $checkEmail = $pdo->prepare(
                'SELECT id FROM users WHERE email = :email LIMIT 1'
            );
            $checkEmail->execute(array(':email' => $email));

            if ($checkEmail->fetch()) {
                $errors[] = 'Email sudah terdaftar.';
            }

            if (empty($errors)) {
                $passwordHash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insertUser = $pdo->prepare(
                    'INSERT INTO users
                    (full_name, username, email, password_hash)
                    VALUES
                    (:full_name, :username, :email, :password_hash)'
                );

                $insertUser->execute(array(
                    ':full_name' => $fullName,
                    ':username' => $username,
                    ':email' => $email,
                    ':password_hash' => $passwordHash
                ));

                // Membuat session login otomatis
                session_regenerate_id(true);

                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $fullName;

                // Memperbarui token CSRF
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                // Langsung menuju beranda
                header('Location: index.php');
                exit;
            }

        } catch (PDOException $exception) {
            $databaseError = true;
        }
    }
}

require __DIR__ . '/pendaftaran.xhtml';
?>
