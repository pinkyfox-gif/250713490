
<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $pdo = require __DIR__ . '/database.php';

        $query = $pdo->prepare(
            "SELECT * FROM admins WHERE username = :username LIMIT 1"
        );

        $query->execute(array(':username' => $username));
        $admin = $query->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: dashboard_admin.php');
            exit;
        } else {
            $error = 'Username atau password admin salah.';
        }

    } catch (PDOException $e) {
        $error = 'Terjadi kesalahan koneksi database.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | Sweet Crumbs</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container form-layout">
    <div class="form-card">
        <h2>Login Admin</h2>
        <p>Masuk untuk mengelola Sweet Crumbs Bakery.</p>

        <?php if ($error !== ''): ?>
            <div class="alert error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="admin_login.php">
            <div class="form-group">
                <label>Username Admin</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="primary-button full-button">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>
