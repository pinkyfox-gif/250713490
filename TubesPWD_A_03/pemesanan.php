<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$produk = array(
    array('id' => 1, 'nama' => 'Butter Croissant', 'harga' => 18000, 'gambar' => 'images/croissant.jpg'),
    array('id' => 2, 'nama' => 'Chocolate Danish', 'harga' => 22000, 'gambar' => 'images/Danish.jpg'),
    array('id' => 3, 'nama' => 'Classic Sourdough', 'harga' => 35000, 'gambar' => 'images/Sourdough.jpg'),
    array('id' => 4, 'nama' => 'Milk Bread', 'harga' => 28000, 'gambar' => 'images/Milk Bread.jpg'),
    array('id' => 5, 'nama' => 'Strawberry Shortcake', 'harga' => 42000, 'gambar' => 'images/Shortcake.jpg'),
    array('id' => 6, 'nama' => 'Chocolate Brownies', 'harga' => 30000, 'gambar' => 'images/Chocolate Brownies.jpg'),
    array('id' => 7, 'nama' => 'Cinnamon Roll', 'harga' => 25000, 'gambar' => 'images/Cinnamon Roll.jpg'),
    array('id' => 8, 'nama' => 'Cheese Bun', 'harga' => 20000, 'gambar' => 'images/Cheese Bun.jpg')
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $id = (int)$_POST['id'];
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]++;
        } else {
            $_SESSION['cart'][$id] = 1;
        }
        header('Location: pemesanan.php');
        exit;
    }

    if (isset($_POST['update_qty'])) {
        $id = (int)$_POST['id'];
        $action = $_POST['action'];
        if ($action === 'plus') {
            $_SESSION['cart'][$id]++;
        } elseif ($action === 'minus') {
            $_SESSION['cart'][$id]--;
            if ($_SESSION['cart'][$id] <= 0) {
                unset($_SESSION['cart'][$id]);
            }
        }
        header('Location: pemesanan.php');
        exit;
    }

    if (isset($_POST['remove_item'])) {
        $id = (int)$_POST['id'];
        unset($_SESSION['cart'][$id]);
        header('Location: pemesanan.php');
        exit;
    }
}

$totalHarga = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    foreach ($produk as $p) {
        if ($p['id'] == $id) {
            $totalHarga += ($p['harga'] * $qty);
            break;
        }
    }
}

require __DIR__ . '/pemesanan.xhtml';
?>