<?php
// Fungsi untuk menangani data XML yang dikirim
function handleSOAPRequest($xml) {
    // Memuat XML
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    if ($dom === false) {
        echo "Invalid XML\n";
        return;
    }

    // Ambil data dari XML
    $id_pemesanan = $dom->getElementsByTagName("IdPemesanan")[0]->nodeValue;
    $metode_pembayaran = $dom->getElementsByTagName("MetodePembayaran")[0]->nodeValue;
    $jumlah_pembayaran = $dom->getElementsByTagName("JumlahPembayaran")[0]->nodeValue;

    // Simpan data atau lakukan sesuatu
    // Contoh: simpan ke database atau log
    // Misalnya, untuk logging
    file_put_contents('log_pembayaran.txt', "ID Pemesanan: $id_pemesanan, Metode: $metode_pembayaran, Jumlah: $jumlah_pembayaran\n", FILE_APPEND);
}

// Menangani request SOAP
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xml = file_get_contents('php://input');
    handleSOAPRequest($xml);
    echo "Data pembayaran diterima.";
} else {
    echo "Invalid request method.";
}
?>
