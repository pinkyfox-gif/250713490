
<?php
session_start();

if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header('Location: pemesanan.php');
    exit;
}

// Mengambil koordinat dari halaman checkout
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';

// Memastikan koordinat tersedia dan valid
if (
    $latitude === '' ||
    $longitude === '' ||
    !is_numeric($latitude) ||
    !is_numeric($longitude) ||
    (float)$latitude < -90 ||
    (float)$latitude > 90 ||
    (float)$longitude < -180 ||
    (float)$longitude > 180
) {
    header('Location: checkout.php');
    exit;
}

$latitude = (float)$latitude;
$longitude = (float)$longitude;

$pdo = require __DIR__ . '/database.php';
$userId = $_SESSION['user_id'];

$produk = array(
    array('id' => 1, 'nama' => 'Butter Croissant', 'harga' => 18000),
    array('id' => 2, 'nama' => 'Chocolate Danish', 'harga' => 22000),
    array('id' => 3, 'nama' => 'Classic Sourdough', 'harga' => 35000),
    array('id' => 4, 'nama' => 'Milk Bread', 'harga' => 28000),
    array('id' => 5, 'nama' => 'Strawberry Shortcake', 'harga' => 42000),
    array('id' => 6, 'nama' => 'Chocolate Brownies', 'harga' => 30000),
    array('id' => 7, 'nama' => 'Cinnamon Roll', 'harga' => 25000),
    array('id' => 8, 'nama' => 'Cheese Bun', 'harga' => 20000)
);

$totalHarga = 0;
$itemsToInsert = array();

foreach ($_SESSION['cart'] as $id => $qty) {
    foreach ($produk as $p) {
        if ($p['id'] == $id) {
            $totalHarga += ($p['harga'] * $qty);

            $itemsToInsert[] = array(
                'nama' => $p['nama'],
                'harga' => $p['harga'],
                'qty' => $qty
            );

            break;
        }
    }
}

try {
    $pdo->beginTransaction();

    // Menyimpan pesanan beserta koordinat pelanggan
    $stmtOrder = $pdo->prepare(
        "INSERT INTO orders
        (user_id, total_harga, status, latitude, longitude)
        VALUES
        (:user_id, :total, 'Diproses', :latitude, :longitude)"
    );

    $stmtOrder->execute(array(
        ':user_id' => $userId,
        ':total' => $totalHarga,
        ':latitude' => $latitude,
        ':longitude' => $longitude
    ));

    $orderId = $pdo->lastInsertId();

    // Menyimpan detail produk
    $stmtItem = $pdo->prepare(
        "INSERT INTO order_items
        (order_id, nama_produk, harga, qty)
        VALUES
        (:order_id, :nama, :harga, :qty)"
    );

    foreach ($itemsToInsert as $item) {
        $stmtItem->execute(array(
            ':order_id' => $orderId,
            ':nama' => $item['nama'],
            ':harga' => $item['harga'],
            ':qty' => $item['qty']
        ));
    }

    $pdo->commit();

    unset($_SESSION['cart']);

    header('Location: status_pesanan.php?id=' . $orderId);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    die('Gagal memproses pesanan: ' . $e->getMessage());
}
?>
