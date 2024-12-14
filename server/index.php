<?php
// Koneksi ke database manajemen_mobil
include 'koneksi.php';

// Koneksi ke database manajemen_user untuk data helpdesk
$conn_user = new mysqli("localhost", "root", "", "manajemen_user");

// Cek koneksi ke database manajemen_user
if ($conn_user->connect_error) {
    die("Koneksi ke database manajemen_user gagal: " . $conn_user->connect_error);
}

// Mengambil data riwayat pemesanan dari API (XML format)
$api_url_pemesanan = "http://10.200.19.102/bis/bis_revisi/user/soap_pembayaran.php";
$response_pemesanan = file_get_contents($api_url_pemesanan);

if ($response_pemesanan === false) {
    die("Gagal mengambil data riwayat pemesanan dari API.");
}

// Parsing XML ke array PHP
libxml_use_internal_errors(true);
$xml = simplexml_load_string($response_pemesanan);
if ($xml === false) {
    echo "Error parsing XML:";
    foreach (libxml_get_errors() as $error) {
        echo "<br>- " . htmlspecialchars($error->message);
    }
    die("Gagal memproses data pemesanan.");
}
$riwayat_pemesanan = json_decode(json_encode($xml), true);

// Mengambil data helpdesk dari REST API
$api_url_helpdesk = "http://10.200.128.220/tst/bis/crud_mobil/rest_helpdesk.php";
$ch = curl_init($api_url_helpdesk);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response_helpdesk = curl_exec($ch);
$http_status_helpdesk = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_status_helpdesk != 200 || !$response_helpdesk) {
    die("Gagal mengambil data helpdesk dari API. HTTP Status: $http_status_helpdesk");
}

// Parsing JSON ke array PHP
$data_helpdesk = json_decode($response_helpdesk, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Gagal memproses data helpdesk dari API: " . json_last_error_msg());
}
$query = "SELECT id_helpdesk, nama_pelanggan, judul, deskripsi, status, tanggal_buat, balasan FROM helpdesk";
$result = $conn->query($query);
$data_helpdesk = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data_helpdesk['data'][] = $row;
    }
} else {
    $data_helpdesk['data'] = [];
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mobil</title>
</head>

<body>
    <h1>Data Mobil</h1>
    <a href="tambah.php">Tambah Mobil</a>
    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Mobil</th>
                <th>Merk Mobil</th>
                <th>Bahan Bakar</th>
                <th>Tahun</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Ambil data mobil
            $sql = "SELECT * FROM mobil";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id_mobil']}</td>
                            <td>{$row['nama_mobil']}</td>
                            <td>{$row['merk_mobil']}</td>
                            <td>{$row['bahan_bakar']}</td>
                            <td>{$row['tahun']}</td>
                            <td>{$row['jumlah']}</td>
                            <td>Rp " . number_format($row['harga'], 2, ',', '.') . "</td>
                            <td>
                                <a href='edit.php?id={$row['id_mobil']}'>Edit</a> |
                                <a href='hapus.php?id={$row['id_mobil']}' onclick='return confirm(\"Hapus data ini?\");'>Hapus</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='8'>Tidak ada data mobil.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Tabel Helpdesk -->

    <h2>Helpdesk</h2>
    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>ID Helpdesk</th>
                <th>Nama Pelanggan</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Balasan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
    <?php
    if (isset($data_helpdesk['data']) && is_array($data_helpdesk['data']) && !empty($data_helpdesk['data'])) {
        foreach ($data_helpdesk['data'] as $row) {
            $nama_pelanggan = isset($row['nama_pelanggan']) ? $row['nama_pelanggan'] : 'Tidak Diketahui';
            $balasan = isset($row['balasan']) ? $row['balasan'] : 'Belum ada balasan'; // Tampilkan balasan dari database
            echo "<tr>
                <td>{$row['id_helpdesk']}</td>
                <td>{$nama_pelanggan}</td>
                <td>{$row['judul']}</td>
                <td>{$row['deskripsi']}</td>
                <td>{$row['status']}</td>
                <td>{$row['tanggal_buat']}</td>
                <td>{$balasan}</td>
                <td><a href='respon_helpdesk.php?id={$row['id_helpdesk']}'>Balas</a></td>
              </tr>";
        }
    } else {
        echo "<tr><td colspan='8'>Tidak ada data helpdesk.</td></tr>";
    }
    ?>
</tbody>

    </table>



    <!-- Tabel Riwayat Pemesanan -->
    <h2>Riwayat Pembayaran</h2>
    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>ID Pemesanan</th>
                <th>Nama Mobil</th>
                <th>Jumlah</th>
                <th>Total Harga</th>
                <th>Metode Pembayaran</th>
                <th>Jumlah Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (isset($riwayat_pemesanan) && !empty($riwayat_pemesanan)) {
                echo "<tr>
                    <td>{$riwayat_pemesanan['id_pemesanan']}</td>
                    <td>{$riwayat_pemesanan['nama_mobil']}</td>
                    <td>{$riwayat_pemesanan['jumlah']}</td>
                    <td>Rp " . number_format($riwayat_pemesanan['total_harga'], 2, ',', '.') . "</td>
                    <td>{$riwayat_pemesanan['metode_pembayaran']}</td>
                    <td>Rp " . number_format($riwayat_pemesanan['jumlah_pembayaran'], 2, ',', '.') . "</td>
                </tr>";
            } else {
                echo "<tr><td colspan='6'>Tidak ada riwayat pemesanan.</td></tr>";
            }
            ?>
        </tbody>
    </table>

</body>

</html>