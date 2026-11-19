var targetNode;

function init() {
    targetNode = document.getElementById("hasil").firstChild;
}

function hitungKurs(id) {
    var inputEl = document.getElementById(id);
    var hasilUsd = parseFloat(inputEl.value) / 18246;
    var hasilTeks = hasilUsd.toFixed(2);

    targetNode.data = hasilTeks;

    var li = document.createElement("li");
    var teks = document.createTextNode("Rp" + inputEl.value + " = US$" + hasilTeks);
    li.appendChild(teks);

    document.getElementById("riwayat").appendChild(li);
}