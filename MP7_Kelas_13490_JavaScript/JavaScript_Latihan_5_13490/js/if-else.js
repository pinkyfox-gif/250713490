function fn_ValForm() {
    var nama = document.getElementById("nama").value;
    var email = document.getElementById("email").value;
    var umur = document.getElementById("umur").value;
    var kategori = document.getElementById("kategori").value;

    var pesan = "";
    var info = "";

    var polaEmail = /^[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9]@[a-z0-9][a-z0-9_\.-]{0,}[a-z0-9][\.][a-z0-9]{2,4}$/;

    if (nama.replace(/\s/g, "") === "") {
        pesan += "- Nama tidak boleh kosong\n";
    }

    if (email === "") {
        pesan += "- Email tidak boleh kosong\n";
    } else if (!polaEmail.test(email)) {
        pesan += "- Format email tidak valid (hanya huruf kecil)\n";
    }

    if (umur === "") {
        pesan += "- Umur tidak boleh kosong\n";
    } else if (!/^[0-9]+$/.test(umur)) {
        pesan += "- Umur harus berupa angka\n";
    } else {
        var angkaUmur = parseInt(umur, 10);
        if (angkaUmur < 10 || angkaUmur > 18) {
            pesan += "- Umur harus antara 10 sampai 18 tahun\n";
        } else if (angkaUmur <= 14) {
            info = "Kategori Junior";
        } else {
            info = "Kategori Senior";
        }
    }

    if (kategori === "") {
        pesan += "- Pilihan lomba tidak boleh kosong\n";
    }

    if (pesan !== "") {
        alert("Terdapat kesalahan:\n" + pesan);
        return false;
    }

    var pilihan = "";
    switch (kategori) {
        case "C":
            pilihan = "Cerdas Cermat";
            break;
        case "P":
            pilihan = "Puisi";
            break;
        case "D":
            pilihan = "Design poster";
            break;
        default:
            pilihan = "";
    }

    alert("Terima kasih " + nama + " ! Anda terdaftar di lomba " + pilihan + " (" + info + ").");
    return true;
}