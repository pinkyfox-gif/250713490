
<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $pdo = require __DIR__ . '/database.php';

        // Memeriksa akun admin terlebih dahulu
        $queryAdmin = $pdo->prepare(
            "SELECT * FROM admins
             WHERE username = :username
             LIMIT 1"
        );

        $queryAdmin->execute(array(
            ':username' => $identity
        ));

        $admin = $queryAdmin->fetch();

        // Jika akun admin ditemukan dan password benar
        if ($admin && password_verify($password, $admin['password_hash'])) {

            session_regenerate_id(true);

            // Menghapus session pelanggan
            unset(
                $_SESSION['user_id'],
                $_SESSION['username'],
                $_SESSION['full_name']
            );

            // Membuat session admin
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: dashboard_admin.php');
            exit;
        }

        // Jika bukan admin, periksa akun pelanggan
        $queryUser = $pdo->prepare(
            "SELECT * FROM users
             WHERE username = :username OR email = :email
             LIMIT 1"
        );

        $queryUser->execute(array(
            ':username' => $identity,
            ':email' => $identity
        ));

        $user = $queryUser->fetch();

        // Jika akun pelanggan ditemukan dan password benar
        if ($user && password_verify($password, $user['password_hash'])) {

            session_regenerate_id(true);

            // Menghapus session admin
            unset(
                $_SESSION['admin_id'],
                $_SESSION['admin_username']
            );

            // Membuat session pelanggan
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];

            header('Location: index.php');
            exit;
        }

        // Jika kedua akun tidak cocok
        $error = 'Username atau password salah.';

    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan saat menghubungkan ke database.';
    }
}

require __DIR__ . '/login.xhtml';
?>
