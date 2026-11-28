<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = require __DIR__ . '/database.php';

function escape_html($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Folder penyimpanan foto profil
$uploadDir = __DIR__ . '/uploads/profil/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mengambil data pengguna
$query = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$query->execute(array(':id' => $userId));
$user = $query->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $message = 'Permintaan tidak valid. Silakan coba lagi.';
        $messageType = 'error';

    // ===== 1. UBAH DATA PROFIL =====
    } elseif ($action === 'update_profile') {

        $nama = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($nama === '' || strlen($nama) > 100) {
            $message = 'Nama wajib diisi dan maksimal 100 karakter.';
            $messageType = 'error';

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            $message = 'Format email tidak valid.';
            $messageType = 'error';

        } elseif (strlen($phone) > 20) {
            $message = 'Nomor telepon maksimal 20 karakter.';
            $messageType = 'error';

        } else {
            try {
                $check = $pdo->prepare(
                    "SELECT id FROM users WHERE email = :email AND id != :id"
                );
                $check->execute(array(':email' => $email, ':id' => $userId));

                if ($check->fetch()) {
                    $message = 'Email sudah digunakan pengguna lain.';
                    $messageType = 'error';
                } else {
                    $update = $pdo->prepare(
                        "UPDATE users SET
                         full_name = :nama,
                         email = :email,
                         phone = :phone,
                         address = :address
                         WHERE id = :id"
                    );
                    $update->execute(array(
                        ':nama' => $nama,
                        ':email' => $email,
                        ':phone' => $phone,
                        ':address' => $address,
                        ':id' => $userId
                    ));

                    $_SESSION['full_name'] = $nama;

                    $query->execute(array(':id' => $userId));
                    $user = $query->fetch();

                    $message = 'Profil berhasil diperbarui!';
                    $messageType = 'success';
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                }
            } catch (PDOException $e) {
                $message = 'Terjadi kesalahan saat memperbarui profil.';
                $messageType = 'error';
            }
        }

    // ===== 2. UBAH PASSWORD =====
    } elseif ($action === 'change_password') {

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $message = 'Password saat ini salah.';
            $messageType = 'error';

        } elseif (strlen($newPassword) < 8) {
            $message = 'Password baru minimal 8 karakter.';
            $messageType = 'error';

        } elseif ($newPassword !== $confirmPassword) {
            $message = 'Konfirmasi password baru tidak sama.';
            $messageType = 'error';

        } elseif ($newPassword === $currentPassword) {
            $message = 'Password baru tidak boleh sama dengan password lama.';
            $messageType = 'error';

        } else {
            try {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

                $update = $pdo->prepare(
                    "UPDATE users SET password_hash = :hash WHERE id = :id"
                );
                $update->execute(array(':hash' => $newHash, ':id' => $userId));

                // Session ID diganti demi keamanan setelah password berubah
                session_regenerate_id(true);

                $query->execute(array(':id' => $userId));
                $user = $query->fetch();

                $message = 'Password berhasil diubah!';
                $messageType = 'success';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            } catch (PDOException $e) {
                $message = 'Terjadi kesalahan saat mengubah password.';
                $messageType = 'error';
            }
        }

    // ===== 3. UPLOAD / GANTI FOTO PROFIL =====
    } elseif ($action === 'upload_photo') {

        $file = $_FILES['photo'] ?? null;

        // Tipe gambar yang diizinkan beserta ekstensi yang akan dipakai
        $allowed = array(
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        );

        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            $message = 'Pilih foto terlebih dahulu.';
            $messageType = 'error';

        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $message = 'Upload gagal. Silakan coba lagi.';
            $messageType = 'error';

        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $message = 'Ukuran foto maksimal 2 MB.';
            $messageType = 'error';

        } else {
            // getimagesize membaca isi file, jadi tidak bisa dikelabui hanya dengan ganti ekstensi
            $imageInfo = @getimagesize($file['tmp_name']);

            if ($imageInfo === false || !isset($allowed[$imageInfo['mime']])) {
                $message = 'File harus berupa gambar JPG, PNG, atau WEBP.';
                $messageType = 'error';

            } else {
                $ext = $allowed[$imageInfo['mime']];
                $newName = 'user_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

                if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                    try {
                        $update = $pdo->prepare(
                            "UPDATE users SET profile_photo = :photo WHERE id = :id"
                        );
                        $update->execute(array(':photo' => $newName, ':id' => $userId));

                        // Hapus foto lama agar folder tidak menumpuk
                        if (!empty($user['profile_photo'])) {
                            $oldFile = $uploadDir . basename($user['profile_photo']);
                            if (is_file($oldFile)) {
                                unlink($oldFile);
                            }
                        }

                        $query->execute(array(':id' => $userId));
                        $user = $query->fetch();

                        $message = 'Foto profil berhasil diperbarui!';
                        $messageType = 'success';
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                    } catch (PDOException $e) {
                        unlink($uploadDir . $newName);
                        $message = 'Terjadi kesalahan saat menyimpan foto.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Foto gagal disimpan ke server.';
                    $messageType = 'error';
                }
            }
        }

    // ===== 4. HAPUS FOTO PROFIL =====
    } elseif ($action === 'delete_photo') {

        if (empty($user['profile_photo'])) {
            $message = 'Kamu belum memiliki foto profil.';
            $messageType = 'error';
        } else {
            try {
                $update = $pdo->prepare(
                    "UPDATE users SET profile_photo = NULL WHERE id = :id"
                );
                $update->execute(array(':id' => $userId));

                $oldFile = $uploadDir . basename($user['profile_photo']);
                if (is_file($oldFile)) {
                    unlink($oldFile);
                }

                $query->execute(array(':id' => $userId));
                $user = $query->fetch();

                $message = 'Foto profil berhasil dihapus.';
                $messageType = 'success';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            } catch (PDOException $e) {
                $message = 'Terjadi kesalahan saat menghapus foto.';
                $messageType = 'error';
            }
        }
    }
}

require __DIR__ . '/profil.xhtml';
?>