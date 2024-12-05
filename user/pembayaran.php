<?php
session_start();
include 'koneksi.php';

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // ID pengguna dari sesi
$id_pemesanan = $_GET['id_pemesanan']; // ID Pemesanan yang dipilih

// Ambil data pemesanan berdasarkan id_pemesanan
$sql_pemesanan = "SELECT * FROM pemesanan WHERE id_pemesanan = $id_pemesanan AND id_pelanggan = $user_id";
$pemesanan = $conn_user->query($sql_pemesanan)->fetch_assoc();

// Mengecek apakah pemesanan ditemukan
if (!$pemesanan) {
    echo "<script>alert('Pemesanan tidak ditemukan!'); window.location='dashboard.php';</script>";
    exit;
}

// Ambil data mobil menggunakan REST API
$api_url = "http://192.168.11.142/tst/bis/crud_mobil/rest_mobil.php";
$response = file_get_contents($api_url);
$data_mobil = json_decode($response, true);

// Periksa status respon API
if ($data_mobil['status'] !== 200) {
    die("Gagal mengambil data mobil: " . $data_mobil['message']);
}

// Cari data mobil yang sesuai dengan id_mobil pada pemesanan
$mobil = array_filter($data_mobil['data'], function ($item) use ($pemesanan) {
    return $item['id_mobil'] == $pemesanan['id_mobil'];
});

$mobil = array_shift($mobil); // Ambil elemen pertama
if (!$mobil) {
    echo "<script>alert('Data mobil tidak ditemukan.'); window.location='dashboard.php';</script>";
    exit;
}

// Proses pembayaran jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metode_pembayaran = $_POST['metode_pembayaran'];
    $jumlah_pembayaran = $_POST['jumlah_pembayaran'];

    // Simpan pembayaran ke tabel pembayaran
    $sql_pembayaran = "INSERT INTO pembayaran (id_pemesanan, metode_pembayaran, jumlah_pembayaran)
                       VALUES ($id_pemesanan, '$metode_pembayaran', $jumlah_pembayaran)";

    if ($conn_user->query($sql_pembayaran) === TRUE) {
        // Kurangi stok mobil
        $jumlah_dipesan = $pemesanan['jumlah'];
        $stok_mobil = $mobil['jumlah'];

        if ($stok_mobil >= $jumlah_dipesan) {
            $new_stok = $stok_mobil - $jumlah_dipesan;

            // Update stok menggunakan REST API (format JSON)
            $update_url = "http://192.168.11.142/tst/bis/crud_mobil/rest_mobil.php";
            $data_update = [
                "id_mobil" => $mobil['id_mobil'],
                "nama_mobil" => $mobil['nama_mobil'],
                "merk_mobil" => $mobil['merk_mobil'],
                "bahan_bakar" => $mobil['bahan_bakar'],
                "tahun" => $mobil['tahun'],
                "jumlah" => $new_stok,
                "harga" => $mobil['harga']
            ];

            // Set opsi HTTP dengan header untuk JSON
            $options = [
                "http" => [
                    "header"  => "Content-type: application/json", // Pastikan headernya JSON
                    "method"  => "PUT",  // Menggunakan metode PUT
                    "content" => json_encode($data_update),  // Encode data dalam format JSON
                ],
            ];

            // Kirimkan request
            $context = stream_context_create($options);
            $response_update = file_get_contents($update_url, false, $context);

            // Cek respons dari API
            if ($response_update === FALSE) {
                echo "<script>alert('Gagal mengupdate stok mobil.'); window.location='dashboard.php';</script>";
            } else {
                $response_data = json_decode($response_update, true);
                if (isset($response_data['status']) && $response_data['status'] == 'success') {
                    echo "<script>alert('Pembayaran berhasil! Stok mobil telah diperbarui.'); window.location='dashboard.php';</script>";
                } else {
                    echo "<script>alert('Gagal memperbarui stok mobil: " . $response_data['message'] . "'); window.location='dashboard.php';</script>";
                }
            }
        } else {
            echo "<script>alert('Stok mobil tidak cukup.'); window.location='dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Pembayaran gagal: {$conn_user->error}');</script>";
    }
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
            <td><?= $pemesanan['id_pemesanan'] ?></td>
        </tr>
        <tr>
            <th>Nama Mobil</th>
            <td><?= $mobil['nama_mobil'] ?></td>
        </tr>
        <tr>
            <th>Jumlah</th>
            <td><?= $pemesanan['jumlah'] ?></td>
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
        <input type="number" name="jumlah_pembayaran" id="jumlah_pembayaran" min="1" value="<?= $pemesanan['total_harga'] ?>" required><br><br>

        <button type="submit">Bayar</button>
    </form>

    <br>
    <a href="dashboard.php">Kembali ke Dashboard</a>
</body>
</html>
