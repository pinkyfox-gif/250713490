
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$pdo = require __DIR__ . '/database.php';

$stmt = $pdo->prepare(
    "SELECT * FROM orders
     WHERE id = :id AND user_id = :user_id"
);

$stmt->execute(array(
    ':id' => $orderId,
    ':user_id' => $_SESSION['user_id']
));

$order = $stmt->fetch();

if (!$order) {
    die("Pesanan tidak ditemukan.");
}

$stmtItems = $pdo->prepare(
    "SELECT * FROM order_items WHERE order_id = :order_id"
);

$stmtItems->execute(array(':order_id' => $orderId));
$items = $stmtItems->fetchAll();

$statusList = array(
    'Diproses',
    'Sedang Disiapkan',
    'Menuju Lokasi',
    'Pesananmu Sampai!'
);

$currentStatusIndex = array_search($order['status'], $statusList);

// Koordinat toko Sweet Crumbs Bakery
$tokoLatitude = -7.77991;
$tokoLongitude = 110.41508;

// Koordinat pelanggan dari database
$userLatitude = $order['latitude'];
$userLongitude = $order['longitude'];

$lokasiTersedia = (
    $userLatitude !== null &&
    $userLongitude !== null
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Status Pesanan | Sweet Crumbs</title>

    <link rel="stylesheet" href="css/style.css">

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        #order-map {
            width: 100%;
            height: 350px;
            border-radius: 12px;
            margin-top: 15px;
            z-index: 1;
        }

        .location-section {
            margin: 25px 0;
        }

        .location-section p {
            color: var(--muted);
            font-size: 14px;
            margin-top: 8px;
        }
    </style>
</head>

<body>

    <div class="navbar">
        <div class="container nav-content">
            <a class="brand" href="index.php">Sweet Crumbs<span>.</span></a>

            <div class="nav-links">
                <a href="index.php">Beranda</a>
                <a href="katalog.php">Katalog</a>
                <a href="pemesanan.php">Keranjang</a>
                <a href="profil.php">Profil</a>
                <a href="logout.php" class="nav-button"
                   onclick="return confirm('Yakin ingin logout?')">
                    Logout
                </a>
            </div>
        </div>
    </div>

    <div class="container" style="max-width: 700px; margin: 50px auto;">

        <div class="cart-box" style="position: static;">

            <h3 style="text-align: center;">Status Pesanan</h3>

            <p style="text-align: center; color: var(--muted);">
                ID Pesanan:
                <strong>
                    #BKY-<?= str_pad($order['id'], 4, '0', STR_PAD_LEFT) ?>
                </strong>
            </p>

            <div class="status-timeline">

                <div class="timeline-step <?= $currentStatusIndex >= 0 ? 'active' : '' ?>">
                    <div class="timeline-icon">✓</div>
                    <div class="timeline-content">
                        <h4>Diproses</h4>
                        <p>Pesananmu sudah diterima dan sedang dicek oleh tim kami.</p>
                    </div>
                </div>

                <div class="timeline-step <?= $currentStatusIndex >= 1 ? 'active' : '' ?>">
                    <div class="timeline-icon">✓</div>
                    <div class="timeline-content">
                        <h4>Sedang Disiapkan</h4>
                        <p>Roti sedang dipanggang dan disiapkan dengan penuh cinta.</p>
                    </div>
                </div>

                <div class="timeline-step <?= $currentStatusIndex >= 2 ? 'active' : '' ?>">
                    <div class="timeline-icon">✓</div>
                    <div class="timeline-content">
                        <h4>Menuju Lokasi</h4>
                        <p>Kurir kami sedang dalam perjalanan membawa pesananmu.</p>
                    </div>
                </div>

                <div class="timeline-step <?= $currentStatusIndex >= 3 ? 'active' : '' ?>">
                    <div class="timeline-icon">✓</div>
                    <div class="timeline-content">
                        <h4>Sampai</h4>
                        <p>Pesanan sudah sampai di lokasi. Selamat menikmati!</p>
                    </div>
                </div>

            </div>

            <hr style="border: 0; border-top: 1px dashed var(--line); margin: 20px 0;">

            <!-- Lokasi Pesanan -->
            <div class="location-section">

                <h4>Lokasi Pesanan</h4>

                <?php if ($lokasiTersedia): ?>

                    <p>
                        Peta menampilkan lokasi toko dan lokasi pengirimanmu.
                    </p>

                    <div id="order-map"></div>

                    <p>
                        <strong>Lokasi pelanggan:</strong><br>
                        Latitude: <?= htmlspecialchars((string)$userLatitude) ?><br>
                        Longitude: <?= htmlspecialchars((string)$userLongitude) ?>
                    </p>

                <?php else: ?>

                    <p>Data lokasi pelanggan tidak tersedia.</p>

                <?php endif; ?>

            </div>

            <hr style="border: 0; border-top: 1px dashed var(--line); margin: 20px 0;">

            <!-- Ringkasan Pesanan -->
            <h4 style="margin-bottom: 15px;">Ringkasan Pesanan</h4>

            <?php foreach ($items as $item): ?>

                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">

                    <span>
                        <?= $item['qty'] ?>x
                        <?= htmlspecialchars($item['nama_produk']) ?>
                    </span>

                    <span>
                        Rp <?= number_format($item['harga'] * $item['qty'], 0, ',', '.') ?>
                    </span>

                </div>

            <?php endforeach; ?>

            <div class="cart-total" style="margin-bottom: 0;">
                <span>Total:</span>
                <span>
                    Rp <?= number_format($order['total_harga'], 0, ',', '.') ?>
                </span>
            </div>

            <a href="pemesanan.php"
               class="btn-checkout"
               style="display: block; text-align: center; text-decoration: none; margin-top: 20px; background: var(--cream); color: var(--brown); border: 1px solid var(--line);">
                Pesan Lagi
            </a>

        </div>
    </div>

    <?php if ($lokasiTersedia): ?>

    <script>
        var tokoLat = -7.77991;
        var tokoLng = 110.41508;

        var userLat = <?= json_encode((float)$userLatitude) ?>;
        var userLng = <?= json_encode((float)$userLongitude) ?>;

        var map = L.map('order-map').setView(
            [userLat, userLng],
            15
        );

        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution: '&copy; OpenStreetMap contributors'
            }
        ).addTo(map);

        L.marker([tokoLat, tokoLng])
            .addTo(map)
            .bindPopup('<b>Sweet Crumbs Bakery</b><br>Kampus 3 UAJY');

        L.marker([userLat, userLng])
            .addTo(map)
            .bindPopup('<b>Lokasi Pelanggan</b>');

        var bounds = L.latLngBounds(
            [tokoLat, tokoLng],
            [userLat, userLng]
        );

        map.fitBounds(bounds, {
            padding: [40, 40]
        });
    </script>

    <?php endif; ?>

</body>
</html>
