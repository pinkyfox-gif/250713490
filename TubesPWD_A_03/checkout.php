
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    header('Location: pemesanan.php');
    exit;
}

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
$itemsCheckout = array();

foreach ($_SESSION['cart'] as $id => $qty) {
    foreach ($produk as $p) {
        if ($p['id'] == $id) {
            $subtotal = $p['harga'] * $qty;
            $totalHarga += $subtotal;

            $itemsCheckout[] = array(
                'nama' => $p['nama'],
                'harga' => $p['harga'],
                'qty' => $qty,
                'subtotal' => $subtotal
            );

            break;
        }
    }
}

// Koordinat simulasi toko di Kampus 3 UAJY
$tokoLatitude = -7.77991;
$tokoLongitude = 110.41508;

require __DIR__ . '/checkout.xhtml';
?>
