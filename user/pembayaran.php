<?php
session_start();
include 'koneksi.php';

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id_pemesanan = isset($_GET['id_pemesanan']) ? $_GET['id_pemesanan'] : null;

// Validasi ID Pemesanan
if (!$id_pemesanan) {
    echo "<script>alert('ID Pemesanan tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

// Ambil data pemesanan berdasarkan id_pemesanan
$sql_pemesanan = "SELECT * FROM pemesanan WHERE id_pemesanan = $id_pemesanan AND id_pelanggan = $user_id";
$result_pemesanan = $conn_user->query($sql_pemesanan);

if ($result_pemesanan && $result_pemesanan->num_rows > 0) {
    $pemesanan = $result_pemesanan->fetch_assoc();
} else {
    echo "<script>alert('Pemesanan tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

// Ambil data mobil menggunakan REST API
$api_url = "http://10.200.128.220/tst/bis/crud_mobil/rest_mobil.php";
$response = file_get_contents($api_url);
$data_mobil = json_decode($response, true);

if (!$response || $data_mobil['status'] !== 200) {
    echo "<script>alert('Gagal mengambil data mobil.'); window.location='dashboard.php';</script>";
    exit;
}

// Cari data mobil yang sesuai dengan id_mobil pada pemesanan
$mobil = array_filter($data_mobil['data'], function ($item) use ($pemesanan) {
    return $item['id_mobil'] == $pemesanan['id_mobil'];
});

$mobil = array_shift($mobil);

if (!$mobil) {
    echo "<script>alert('Data mobil tidak ditemukan.'); window.location='dashboard.php';</script>";
    exit;
}

// Fungsi untuk membuat file XML
function simpanDataKeXML($pemesanan, $mobil, $metode_pembayaran, $jumlah_pembayaran)
{
    $xml = new DOMDocument("1.0", "UTF-8");
    $xml->formatOutput = true;

    // Elemen root
    $root = $xml->createElement("pembayaran");
    $xml->appendChild($root);

    // Tambahkan elemen data pemesanan
    $idPemesanan = $xml->createElement("id_pemesanan", $pemesanan['id_pemesanan']);
    $root->appendChild($idPemesanan);

    $namaMobil = $xml->createElement("nama_mobil", $mobil['nama_mobil']);
    $root->appendChild($namaMobil);

    $jumlah = $xml->createElement("jumlah", $pemesanan['jumlah']);
    $root->appendChild($jumlah);

    $totalHarga = $xml->createElement("total_harga", $pemesanan['total_harga']);
    $root->appendChild($totalHarga);

    // Tambahkan elemen data pembayaran
    $metode = $xml->createElement("metode_pembayaran", $metode_pembayaran);
    $root->appendChild($metode);

    $jumlahBayar = $xml->createElement("jumlah_pembayaran", $jumlah_pembayaran);
    $root->appendChild($jumlahBayar);

    // Simpan file XML
    $filePath = "soap_pembayaran.php"; // Ganti ekstensi menjadi .xml agar sesuai dengan format XML
    $xml->save($filePath);
}

// Proses pembayaran jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $jumlah_pembayaran = $_POST['jumlah_pembayaran'];

    // Simpan data ke file XML
    simpanDataKeXML($pemesanan, $mobil, $metode_pembayaran, $jumlah_pembayaran);

    // Update stok mobil
    $stok_baru = $mobil['stok'] - $pemesanan['jumlah'];
    $update_result = updateStokMobil($mobil['id_mobil'], $stok_baru);

    if ($update_result) {
        echo "<script>alert('Pembayaran berhasil dan stok mobil berhasil diperbarui.'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Pembayaran berhasil, tetapi gagal memperbarui stok mobil.'); window.location='dashboard.php';</script>";
    }
}



// Fungsi untuk memperbarui stok mobil di REST API
function updateStokMobil($id_mobil, $stok_baru)
{
    $api_url = "http://10.200.128.220/tst/bis/crud_mobil/rest_mobil.php";

    $data = [
        'id_mobil' => $id_mobil,
        'stok' => $stok_baru
    ];

    $jsonData = json_encode($data);

    // Inisialisasi cURL
    $ch = curl_init($api_url);

    // Set opsi cURL
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); // Mengatur metode ke PUT
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Data JSON
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Mendapatkan respons sebagai string
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json', // Header untuk JSON
        'Content-Length: ' . strlen($jsonData) // Panjang konten
    ]);

    // Eksekusi permintaan
    $response = curl_exec($ch);

    // Tutup cURL
    curl_close($ch);

    // var_dump($response);
    // exit;

   return $response;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
</head>

<body>
    <h1>Pembayaran Pemesanan</h1>
    <h2>Detail Pemesanan</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID Pemesanan</th>
            <td><?= htmlspecialchars($pemesanan['id_pemesanan']) ?></td>
        </tr>
        <tr>
            <th>Nama Mobil</th>
            <td><?= htmlspecialchars($mobil['nama_mobil']) ?></td>
        </tr>
        <tr>
            <th>Jumlah</th>
            <td><?= htmlspecialchars($pemesanan['jumlah']) ?></td>
        </tr>
        <tr>
            <th>Total Harga</th>
            <td>Rp <?= number_format($pemesanan['total_harga'], 2, ',', '.') ?></td>
        </tr>
    </table>

    <h2>Form Pembayaran</h2>
    <form method="POST">
        <label for="metode_pembayaran">Metode Pembayaran:</label><br>
        <select name="metode_pembayaran" required>
            <option value="">-- Pilih Metode --</option>
            <option value="Transfer Bank">Transfer Bank</option>
            <option value="Kredit">Kredit</option>
            <option value="Cash">Cash</option>
        </select><br><br>

        <label for="jumlah_pembayaran">Jumlah Pembayaran:</label><br>
        <input type="number" name="jumlah_pembayaran" id="jumlah_pembayaran" min="1"
            value="<?= $pemesanan['total_harga'] ?>" required><br><br>

        <button type="submit">Bayar</button>
    </form>

    <br>
    <a href="dashboard.php">Kembali ke Dashboard</a>
</body>

</html>
