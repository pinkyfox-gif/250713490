
<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $id = (int)$_POST['id'];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }

    header('Location: pemesanan.php');
    exit;
}

$produk = array(
    array(
        'id' => 1,
        'nama' => 'Butter Croissant',
        'kategori' => 'Pastry',
        'harga' => 18000,
        'deskripsi' => 'Croissant renyah dengan aroma butter.',
        'gambar' => 'images/croissant.jpg'
    ),
    array(
        'id' => 2,
        'nama' => 'Chocolate Danish',
        'kategori' => 'Pastry',
        'harga' => 22000,
        'deskripsi' => 'Pastry dengan isian cokelat.',
        'gambar' => 'images/Danish.jpg'
    ),
    array(
        'id' => 3,
        'nama' => 'Classic Sourdough',
        'kategori' => 'Roti',
        'harga' => 35000,
        'deskripsi' => 'Roti sourdough dengan tekstur khas.',
        'gambar' => 'images/Sourdough.jpg'
    ),
    array(
        'id' => 4,
        'nama' => 'Milk Bread',
        'kategori' => 'Roti',
        'harga' => 28000,
        'deskripsi' => 'Roti lembut dengan rasa susu.',
        'gambar' => 'images/Milk Bread.jpg'
    ),
    array(
        'id' => 5,
        'nama' => 'Strawberry Shortcake',
        'kategori' => 'Cake',
        'harga' => 42000,
        'deskripsi' => 'Cake lembut dengan krim dan stroberi.',
        'gambar' => 'images/Shortcake.jpg'
    ),
    array(
        'id' => 6,
        'nama' => 'Chocolate Brownies',
        'kategori' => 'Cake',
        'harga' => 30000,
        'deskripsi' => 'Brownies cokelat dengan tekstur lembut.',
        'gambar' => 'images/Chocolate Brownies.jpg'
    ),
    array(
        'id' => 7,
        'nama' => 'Cinnamon Roll',
        'kategori' => 'Pastry',
        'harga' => 25000,
        'deskripsi' => 'Roti gulung dengan aroma kayu manis.',
        'gambar' => 'images/Cinnamon Roll.jpg'
    ),
    array(
        'id' => 8,
        'nama' => 'Cheese Bun',
        'kategori' => 'Roti',
        'harga' => 20000,
        'deskripsi' => 'Roti lembut dengan topping keju.',
        'gambar' => 'images/Cheese Bun.jpg'
    )
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Produk | Sweet Crumbs</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- Navbar -->
<div class="navbar">
    <div class="container nav-content">

        <a class="brand" href="index.php">
            Sweet Crumbs<span>.</span>
        </a>

        <div class="nav-links">

            <a href="index.php"
               class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">
                Beranda
            </a>

            <a href="katalog.php"
               class="<?= (basename($_SERVER['PHP_SELF']) == 'katalog.php') ? 'active' : '' ?>">
                Katalog
            </a>

            <?php if (isset($_SESSION['user_id'])): ?>

                <a href="pemesanan.php">
                    Keranjang
                </a>

                <a href="riwayat_pesanan.php"
                   class="<?= (basename($_SERVER['PHP_SELF']) == 'riwayat_pesanan.php') ? 'active' : '' ?>">
                    Riwayat
                </a>

                <a href="profil.php">
                    Profil
                </a>

                <a href="logout.php"
                   class="nav-button"
                   onclick="return confirm('Yakin ingin logout?')">
                    Logout
                </a>

            <?php else: ?>

                <a href="login.php">
                    Login
                </a>

                <a class="nav-button" href="pendaftaran.php">
                    Daftar
                </a>

            <?php endif; ?>

        </div>
    </div>
</div>


<!-- Katalog Produk -->
<div class="container catalog-container">

    <div class="catalog-heading">
        <p class="eyebrow">OUR PRODUCTS</p>
        <h1>Katalog Bakery</h1>
        <p>Temukan berbagai pilihan roti, pastry, dan cake favoritmu.</p>
    </div>

    <input
        type="text"
        id="searchProduk"
        placeholder="Cari produk bakery..."
        onkeyup="tampilkanProduk()"
    >

    <div class="catalog-filter">
        <button class="active" onclick="filterProduk('Semua', this)">Semua</button>
        <button onclick="filterProduk('Roti', this)">Roti</button>
        <button onclick="filterProduk('Pastry', this)">Pastry</button>
        <button onclick="filterProduk('Cake', this)">Cake</button>
    </div>

    <div class="product-grid" id="productGrid">

        <?php foreach ($produk as $item): ?>

        <div
            class="product-card"
            data-kategori="<?= htmlspecialchars($item['kategori']) ?>"
            style="cursor: pointer;"
            onclick="bukaModal(
                '<?= $item['id'] ?>',
                '<?= htmlspecialchars($item['nama'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($item['kategori'], ENT_QUOTES) ?>',
                '<?= htmlspecialchars($item['deskripsi'], ENT_QUOTES) ?>',
                '<?= $item['harga'] ?>',
                '<?= htmlspecialchars($item['gambar'], ENT_QUOTES) ?>'
            )"
        >

            <div class="product-image">
                <img
                    src="<?= htmlspecialchars($item['gambar']) ?>"
                    alt="<?= htmlspecialchars($item['nama']) ?>"
                    onerror="this.style.display='none'; this.parentElement.classList.add('image-error');"
                >
            </div>

            <div class="product-info">

                <span class="product-category">
                    <?= htmlspecialchars($item['kategori']) ?>
                </span>

                <h3><?= htmlspecialchars($item['nama']) ?></h3>

                <p><?= htmlspecialchars($item['deskripsi']) ?></p>

                <h4>
                    Rp <?= number_format($item['harga'], 0, ',', '.') ?>
                </h4>

            </div>
        </div>

        <?php endforeach; ?>

    </div>

    <p id="tidakDitemukan" style="display:none; text-align:center; margin-top:30px;">
        Produk tidak ditemukan.
    </p>

</div>


<!-- Modal Detail Produk -->
<div id="productModal" class="modal-overlay">

    <div class="modal-content">

        <span class="close-btn" onclick="tutupModal()">&times;</span>

        <div class="modal-body">

            <div class="modal-image">
                <img id="modalGambar" src="" alt="Produk">
            </div>

            <div class="modal-info">

                <span id="modalKategori" class="product-category"></span>

                <h2 id="modalNama"></h2>

                <p id="modalDeskripsi"></p>

                <h3 id="modalHarga" class="price"></h3>

                <form method="POST" action="katalog.php">

                    <input type="hidden" name="id" id="modalId">

                    <button
                        type="submit"
                        name="add_to_cart"
                        class="btn-checkout"
                        style="margin-top: 20px;"
                    >
                        + Masukkan ke Keranjang
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>


<!-- Footer -->
<div class="footer">
    <p>Sweet Crumbs Bakery &copy; 2026</p>
</div>


<!-- JavaScript -->
<script>
let kategoriAktif = 'Semua';

function filterProduk(kategori, tombol) {
    kategoriAktif = kategori;

    let tombolFilter = document.querySelectorAll('.catalog-filter button');

    tombolFilter.forEach(function(btn) {
        btn.classList.remove('active');
    });

    tombol.classList.add('active');

    tampilkanProduk();
}

function tampilkanProduk() {
    let input = document.getElementById('searchProduk').value.toLowerCase();
    let produk = document.querySelectorAll('.product-card');
    let jumlahTampil = 0;

    produk.forEach(function(item) {
        let nama = item.querySelector('h3').textContent.toLowerCase();
        let kategori = item.dataset.kategori;

        let cocokNama = nama.includes(input);
        let cocokKategori = kategoriAktif === 'Semua' || kategori === kategoriAktif;

        if (cocokNama && cocokKategori) {
            item.style.display = 'flex';
            jumlahTampil++;
        } else {
            item.style.display = 'none';
        }
    });

    document.getElementById('tidakDitemukan').style.display =
        jumlahTampil === 0 ? 'block' : 'none';
}

function bukaModal(id, nama, kategori, deskripsi, harga, gambar) {
    document.getElementById('modalId').value = id;
    document.getElementById('modalNama').innerText = nama;
    document.getElementById('modalKategori').innerText = kategori;
    document.getElementById('modalDeskripsi').innerText = deskripsi;
    document.getElementById('modalGambar').src = gambar;

    let formatHarga = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(harga);

    document.getElementById('modalHarga').innerText = formatHarga;

    document.getElementById('productModal').style.display = 'flex';
}

function tutupModal() {
    document.getElementById('productModal').style.display = 'none';
}

window.onclick = function(event) {
    let modal = document.getElementById('productModal');

    if (event.target == modal) {
        tutupModal();
    }
}
</script>

</body>
</html>
