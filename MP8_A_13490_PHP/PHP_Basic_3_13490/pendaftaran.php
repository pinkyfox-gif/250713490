<?php
$paketList = [
    '1' => '1 Bulan',
    '3' => '3 Bulan',
];

$keuntungan = [
    '1' => 'Potongan 50% untuk pembelian produk tertentu.',
    '3' => 'Gratis cemilan hewan peliharaan untuk 3 bulan pertama.',
];

$nama = '';
$npm = '';
$paket = '';
$errors = [];
$benefit = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $npm = trim($_POST['npm'] ?? '');
    $paket = $_POST['paket'] ?? '';

    if ($nama === '' || $npm === '' || $paket === '') {
        $errors[] = 'Nama, NPM, dan paket membership tidak boleh kosong.';
    }

    if ($nama !== '' && !preg_match('/^[A-Za-z ]+$/', $nama)) {
        $errors[] = 'Nama hanya boleh mengandung huruf dan spasi.';
    }

    if ($npm !== '' && !preg_match('/^[0-9]+$/', $npm)) {
        $errors[] = 'NPM hanya boleh mengandung angka.';
    }

    if ($paket !== '' && !isset($paketList[$paket])) {
        $errors[] = 'Paket membership tidak valid.';
    }

    if (empty($errors)) {
        $benefit = $keuntungan[$paket];
    }
}
?>
<h2>Pendaftaran Membership</h2>

<form action="?menu=M" method="post">
    <div>
        <label for="nama">Nama</label><br />
        <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($nama); ?>" />
    </div>
    <div>
        <label for="npm">NPM</label><br />
        <input type="text" id="npm" name="npm" value="<?php echo htmlspecialchars($npm); ?>" />
    </div>
    <div>
        <label for="paket">Paket Membership</label><br />
        <select id="paket" name="paket">
            <option value="">-- Pilih Paket --</option>
            <?php foreach ($paketList as $kode => $label): ?>
                <option value="<?php echo htmlspecialchars($kode); ?>"<?php echo ($paket === (string) $kode) ? ' selected="selected"' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <button type="submit">Daftar</button>
    </div>
</form>

<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <h3>Informasi Pendaftaran</h3>
        <p>Nama: <?php echo htmlspecialchars($nama); ?></p>
        <p>NPM: <?php echo htmlspecialchars($npm); ?></p>
        <p>Keuntungan: <?php echo htmlspecialchars($benefit); ?></p>
        <p>Paket Membership: <?php echo htmlspecialchars($paketList[$paket]); ?></p>
        <p>Selamat! Anda telah berhasil mendaftar sebagai member dengan benefit: <?php echo htmlspecialchars($benefit); ?></p>
    <?php endif; ?>
<?php else: ?>
    <p>Silahkan lengkapi formulir di atas.</p>
<?php endif; ?>