<?php
session_start();
include 'koneksi.php';

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // ID pengguna dari sesi

// Ambil data pengguna dari database manajemen_user
$sql_user = "SELECT * FROM pelanggan WHERE id_pelanggan = $user_id";
$user = $conn_user->query($sql_user)->fetch_assoc();

// Ambil data mobil menggunakan REST API
$api_url = "http://192.168.11.142/tst/bis/crud_mobil/rest_mobil.php";
$response = file_get_contents($api_url);
$data_mobil = json_decode($response, true);

// Periksa status respon API
if ($data_mobil['status'] !== 200) {
    die("Gagal mengambil data mobil: " . $data_mobil['message']);
}

// Proses form pemesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_mobil = $_POST['id_mobil'];
    $jumlah = $_POST['jumlah'];

    // Cari data mobil dari hasil API
    $mobil = array_filter($data_mobil['data'], function ($item) use ($id_mobil) {
        return $item['id_mobil'] == $id_mobil;
    });

    if (!$mobil) {
        echo "<script>alert('Mobil tidak ditemukan.');</script>";
        exit;
    }

    $mobil = array_shift($mobil); // Ambil elemen pertama
    $harga_per_unit = $mobil['harga'];
    $total_harga = $harga_per_unit * $jumlah;

    // Simpan pemesanan ke tabel pemesanan di database manajemen_user
    $sql_pemesanan = "INSERT INTO pemesanan (id_pelanggan, id_mobil, jumlah, total_harga)
                      VALUES ($user_id, $id_mobil, $jumlah, $total_harga)";

    if ($conn_user->query($sql_pemesanan) === TRUE) {
        echo "<script>alert('Pemesanan berhasil!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Pemesanan gagal: {$conn_user->error}');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>

<body>
    <h1>Selamat datang, <?= $user['nama_pelanggan'] ?>!</h1>

    <h2>Informasi Pengguna</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <td><?= $user['id_pelanggan'] ?></td>
        </tr>
        <tr>
            <th>Nama</th>
            <td><?= $user['nama_pelanggan'] ?></td>
        </tr>
        <tr>
            <th>Nomor Telepon</th>
            <td><?= $user['nomer_telephone'] ?></td>
        </tr>
        <tr>
            <th>Username</th>
            <td><?= $user['username'] ?></td>
        </tr>
    </table>

    <h2>Detail Mobil</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Mobil</th>
                <th>Nama Mobil</th>
                <th>Harga</th>
                <th>Stok</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data_mobil['data'] as $mobil): ?>
                <tr>
                    <td><?= $mobil['id_mobil'] ?></td>
                    <td><?= $mobil['nama_mobil'] ?></td>
                    <td>Rp <?= number_format($mobil['harga'], 2, ',', '.') ?></td>
                    <td><?= $mobil['jumlah'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Form Pemesanan</h2>
    <form method="POST">
        <label for="id_mobil">Pilih Mobil:</label><br>
        <select name="id_mobil" id="id_mobil" required>
            <option value="">-- Pilih Mobil --</option>
            <?php foreach ($data_mobil['data'] as $mobil): ?>
                <option value="<?= $mobil['id_mobil'] ?>">
                    <?= $mobil['nama_mobil'] ?> - Rp <?= number_format($mobil['harga'], 2, ',', '.') ?> - Stok: <?= $mobil['jumlah'] ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="jumlah">Jumlah:</label><br>
        <input type="number" name="jumlah" id="jumlah" min="1" required><br><br>

        <button type="submit">Pesan</button>
    </form>

    <h2>Riwayat Pemesanan</h2>
<table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>ID Pemesanan</th>
            <th>Nama Mobil</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Tanggal Pemesanan</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_pemesanan = "SELECT * FROM pemesanan WHERE id_pelanggan = $user_id";
        $result_pemesanan = $conn_user->query($sql_pemesanan);

        if ($result_pemesanan && $result_pemesanan->num_rows > 0):
            while ($row = $result_pemesanan->fetch_assoc()):
                // Cari nama mobil berdasarkan id_mobil dari API
                $mobil = array_filter($data_mobil['data'], function ($item) use ($row) {
                    return $item['id_mobil'] == $row['id_mobil'];
                });

                $mobil = array_shift($mobil); // Ambil elemen pertama yang cocok
                $nama_mobil = $mobil ? $mobil['nama_mobil'] : 'Tidak ditemukan';
        ?>
                <tr>
                    <td><?= $row['id_pemesanan'] ?></td>
                    <td><?= $nama_mobil ?></td>
                    <td><?= $row['jumlah'] ?></td>
                    <td>Rp <?= number_format($row['total_harga'], 2, ',', '.') ?></td>
                    <td><?= $row['tanggal_pemesanan'] ?></td>
                    <td>
                        <a href="pembayaran.php?id_pemesanan=<?= $row['id_pemesanan'] ?>">Bayar</a>
                    </td>
                </tr>
        <?php
            endwhile;
        else:
        ?>
            <tr>
                <td colspan="6">Belum ada pemesanan.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


    <h2>Ajukan Helpdesk</h2>
    <form method="POST" action="ajukan_helpdesk.php">
        <label for="judul">Judul:</label><br>
        <input type="text" name="judul" id="judul" required><br><br>

        <label for="deskripsi">Deskripsi:</label><br>
        <textarea name="deskripsi" id="deskripsi" rows="4" required></textarea><br><br>

        <button type="submit">Kirim Tiket</button>
    </form>

    <h2>Riwayat Tiket Helpdesk</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Tiket</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Respons Admin</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql_helpdesk = "SELECT h.id_helpdesk, h.judul, h.deskripsi, h.status, h.tanggal_buat, 
                                r.respon AS respon_admin
                             FROM manajemen_mobil.helpdesk h
                             LEFT JOIN manajemen_user.respon_helpdesk r ON h.id_helpdesk = r.id_helpdesk
                             WHERE h.id_pelanggan = $user_id";
            $result_helpdesk = $conn_user->query($sql_helpdesk);

            if ($result_helpdesk->num_rows > 0):
                while ($row = $result_helpdesk->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_helpdesk'] ?></td>
                        <td><?= $row['judul'] ?></td>
                        <td><?= $row['deskripsi'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td><?= $row['respon_admin'] ? $row['respon_admin'] : 'Belum ada respons' ?></td>
                        <td><?= $row['tanggal_buat'] ?></td>
                    </tr>
                <?php endwhile;
            else: ?>
                <tr>
                    <td colspan="6">Belum ada tiket helpdesk.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <br>
    <a href="logout.php">Logout</a>
</body>

</html>
