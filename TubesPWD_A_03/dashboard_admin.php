
<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$pdo = require __DIR__ . '/database.php';

$pesan = '';
$jenisPesan = '';

$statusList = array(
    'Diproses',
    'Sedang Disiapkan',
    'Menuju Lokasi',
    'Pesananmu Sampai!'
);

// Proses perubahan status pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    if ($orderId > 0 && in_array($status, $statusList, true)) {
        $update = $pdo->prepare(
            "UPDATE orders SET status = :status WHERE id = :id"
        );

        $update->execute(array(
            ':status' => $status,
            ':id' => $orderId
        ));

        if ($update->rowCount() > 0) {
            $pesan = 'Status pesanan berhasil diperbarui!';
            $jenisPesan = 'success';
        } else {
            $cekPesanan = $pdo->prepare(
                "SELECT id FROM orders WHERE id = :id"
            );

            $cekPesanan->execute(array(':id' => $orderId));

            if ($cekPesanan->fetch()) {
                $pesan = 'Status pesanan tidak mengalami perubahan.';
                $jenisPesan = 'success';
            } else {
                $pesan = 'Pesanan tidak ditemukan.';
                $jenisPesan = 'error';
            }
        }
    } else {
        $pesan = 'Data perubahan status tidak valid.';
        $jenisPesan = 'error';
    }
}

// Menghitung total pelanggan
$totalUsers = $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

// Menghitung total pesanan
$totalOrders = $pdo->query(
    "SELECT COUNT(*) FROM orders"
)->fetchColumn();

// Menghitung total pendapatan
$totalPendapatan = $pdo->query(
    "SELECT COALESCE(SUM(total_harga), 0) FROM orders"
)->fetchColumn();

// Mengambil daftar pesanan beserta lokasi pelanggan
$query = $pdo->query(
    "SELECT orders.*, users.full_name
     FROM orders
     JOIN users ON orders.user_id = users.id
     ORDER BY orders.created_at DESC"
);

$pesanan = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin | Sweet Crumbs</title>

    <link rel="stylesheet" href="css/style.css">

    <style>
        .location-link {
            display: inline-block;
            padding: 8px 12px;
            background: #f5e6d8;
            color: #9b6038;
            border-radius: 7px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .location-link:hover {
            background: #ead2bd;
        }

        .location-unavailable {
            color: #999;
            font-size: 12px;
        }
    </style>
</head>

<body class="admin-page">

    <nav class="navbar">
        <div class="container nav-content">

            <a href="dashboard_admin.php" class="brand">
                Sweet Crumbs<span>.</span>
            </a>

            <div class="nav-links">
                <span class="admin-label">Administrator</span>

                <a href="logout_admin.php" class="nav-button"
                   onclick="return confirm('Yakin ingin logout?')">
                    Logout
                </a>
            </div>

        </div>
    </nav>

    <main class="container admin-container">

        <section class="admin-heading">
            <div>
                <p class="eyebrow">SWEET CRUMBS BAKERY</p>

                <h1>Dashboard Admin</h1>

                <p class="admin-welcome">
                    Selamat datang,
                    <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong>!
                    Kelola toko dan pantau pesanan pelanggan di sini.
                </p>
            </div>
        </section>

        <section class="admin-summary">

            <div class="admin-stat-card">
                <div class="admin-stat-icon">👥</div>

                <div>
                    <p>Total Pelanggan</p>
                    <h2><?= number_format($totalUsers, 0, ',', '.') ?></h2>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-icon">🛍️</div>

                <div>
                    <p>Total Pesanan</p>
                    <h2><?= number_format($totalOrders, 0, ',', '.') ?></h2>
                </div>
            </div>

            <div class="admin-stat-card">
                <div class="admin-stat-icon">💰</div>

                <div>
                    <p>Total Pendapatan</p>
                    <h2>
                        Rp <?= number_format($totalPendapatan, 0, ',', '.') ?>
                    </h2>
                </div>
            </div>

        </section>

        <section class="admin-orders">

            <div class="admin-section-heading">
                <div>
                    <p class="eyebrow">MANAJEMEN TOKO</p>
                    <h2>Daftar Pesanan</h2>
                    <p>Pantau lokasi dan perbarui status pesanan pelanggan.</p>
                </div>

                <span class="order-count">
                    <?= number_format($totalOrders, 0, ',', '.') ?> Pesanan
                </span>
            </div>

            <?php if ($pesan !== ''): ?>
                <div class="alert <?= htmlspecialchars($jenisPesan) ?>">
                    <?= htmlspecialchars($pesan) ?>
                </div>
            <?php endif; ?>

            <div class="admin-table-wrapper">

                <table class="admin-table">

                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Nama Pelanggan</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Lokasi Pelanggan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($pesanan)): ?>

                            <tr>
                                <td colspan="7" class="empty-orders">
                                    <div>🥐</div>
                                    <strong>Belum ada pesanan</strong>
                                    <p>Pesanan pelanggan akan muncul di sini.</p>
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($pesanan as $item): ?>

                                <tr>
                                    <td>
                                        <strong>
                                            #<?= (int) $item['id'] ?>
                                        </strong>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['full_name']) ?>
                                    </td>

                                    <td class="order-price">
                                        Rp <?= number_format($item['total_harga'], 0, ',', '.') ?>
                                    </td>

                                    <td>
                                        <span class="status-label">
                                            <?= htmlspecialchars($item['status']) ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?= date(
                                            'd-m-Y H:i',
                                            strtotime($item['created_at'])
                                        ) ?>
                                    </td>

                                    <td>
                                        <?php
                                        $lat = $item['latitude'];
                                        $lng = $item['longitude'];
                                        ?>

                                        <?php if (
                                            $lat !== null &&
                                            $lng !== null &&
                                            is_numeric($lat) &&
                                            is_numeric($lng)
                                        ): ?>

                                            <a
                                                class="location-link"
                                                href="https://www.openstreetmap.org/?mlat=<?= urlencode((string)$lat) ?>&mlon=<?= urlencode((string)$lng) ?>#map=17/<?= urlencode((string)$lat) ?>/<?= urlencode((string)$lng) ?>"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                Lihat Lokasi
                                            </a>

                                        <?php else: ?>

                                            <span class="location-unavailable">
                                                Belum tersedia
                                            </span>

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <form method="POST"
                                              action="dashboard_admin.php"
                                              class="status-form">

                                            <input
                                                type="hidden"
                                                name="order_id"
                                                value="<?= (int) $item['id'] ?>"
                                            >

                                            <select name="status" required>
                                                <?php foreach ($statusList as $status): ?>
                                                    <option
                                                        value="<?= htmlspecialchars($status) ?>"
                                                        <?= $item['status'] === $status ? 'selected' : '' ?>
                                                    >
                                                        <?= htmlspecialchars($status) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>

                                            <button
                                                type="submit"
                                                name="update_status"
                                                class="admin-save-button"
                                            >
                                                Simpan
                                            </button>

                                        </form>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <div class="footer">
        <p>Sweet Crumbs Bakery &#169; 2026</p>
    </div>

</body>
</html>
