<?php
session_start();
include 'koneksi.php';

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data pengguna dari database
$sql_user = "SELECT * FROM pelanggan WHERE id_pelanggan = ?";
$stmt_user = $conn_user->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();

// Ambil data mobil menggunakan REST API
$api_url = "http://10.200.128.220/tst/bis/crud_mobil/rest_mobil.php";
$response = @file_get_contents($api_url);

if ($response === false || ($data_mobil = json_decode($response, true)) === null || $data_mobil['status'] !== 200) {
    die("Gagal mengambil data mobil dari API.");
}

// Proses form pemesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id_mobil'], $_POST['jumlah'])) {
        $id_mobil = (int)$_POST['id_mobil'];
        $jumlah = (int)$_POST['jumlah'];

        if ($jumlah <= 0) {
            echo "<script>alert('Jumlah harus lebih dari 0.');</script>";
            exit;
        }

        // Cari mobil berdasarkan ID
        $mobil = array_values(array_filter($data_mobil['data'], function ($item) use ($id_mobil) {
            return $item['id_mobil'] == $id_mobil;
        }));

        if (empty($mobil)) {
            echo "<script>alert('Mobil tidak ditemukan.');</script>";
            exit;
        }

        $mobil = $mobil[0];
        $total_harga = $mobil['harga'] * $jumlah;

        $sql_pemesanan = "INSERT INTO pemesanan (id_pelanggan, id_mobil, jumlah, total_harga) VALUES (?, ?, ?, ?)";
        $stmt_pemesanan = $conn_user->prepare($sql_pemesanan);
        $stmt_pemesanan->bind_param("iiii", $user_id, $id_mobil, $jumlah, $total_harga);

        if ($stmt_pemesanan->execute()) {
            echo "<script>alert('Pemesanan berhasil!'); window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Pemesanan gagal: {$conn_user->error}');</script>";
        }
    }

    // Proses form helpdesk
    if (isset($_POST['judul'], $_POST['deskripsi'])) {
        $judul = trim($_POST['judul']);
        $deskripsi = trim($_POST['deskripsi']);

        if (empty($judul) || empty($deskripsi)) {
            echo "<script>alert('Judul dan deskripsi harus diisi.');</script>";
            exit;
        }

        $rest_url = "http://10.200.128.220/tst/bis/crud_mobil/rest_helpdesk.php";
        $data = [
            'nama_pelanggan' => $user['nama_pelanggan'],
            'judul' => $judul,
            'deskripsi' => $deskripsi
        ];

        $ch = curl_init($rest_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo "<script>alert('cURL Error: " . curl_error($ch) . "');</script>";
        } else {
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($http_code === 200) {
                $response_data = json_decode($response, true);
                if ($response_data['status'] === 200) {
                    echo "<script>alert('Tiket Helpdesk berhasil dikirim!'); window.location='dashboard.php';</script>";
                } else {
                    echo "<script>alert('Gagal mengirim tiket: " . $response_data['message'] . "');</script>";
                }
            } else {
                echo "<script>alert('Gagal menghubungi API. Status HTTP: $http_code');</script>";
            }
        }

        curl_close($ch);
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
    <h1>Selamat datang, <?= htmlspecialchars($user['nama_pelanggan']) ?>!</h1>

    <h2>Informasi Pengguna</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>ID</th>
            <td><?= htmlspecialchars($user['id_pelanggan']) ?></td>
        </tr>
        <tr>
            <th>Nama</th>
            <td><?= htmlspecialchars($user['nama_pelanggan']) ?></td>
        </tr>
        <tr>
            <th>Nomor Telepon</th>
            <td><?= htmlspecialchars($user['nomer_telephone']) ?></td>
        </tr>
        <tr>
            <th>Username</th>
            <td><?= htmlspecialchars($user['username']) ?></td>
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
                    <td><?= htmlspecialchars($mobil['id_mobil']) ?></td>
                    <td><?= htmlspecialchars($mobil['nama_mobil']) ?></td>
                    <td>Rp <?= number_format($mobil['harga'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($mobil['jumlah']) ?></td>
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
                <option value="<?= htmlspecialchars($mobil['id_mobil']) ?>">
                    <?= htmlspecialchars($mobil['nama_mobil']) ?> - Rp <?= number_format($mobil['harga'], 2, ',', '.') ?> -
                    Stok: <?= htmlspecialchars($mobil['jumlah']) ?>
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
            $sql_pemesanan = "SELECT * FROM pemesanan WHERE id_pelanggan = ?";
            $stmt_pemesanan = $conn_user->prepare($sql_pemesanan);
            $stmt_pemesanan->bind_param("i", $user_id);
            $stmt_pemesanan->execute();
            $result_pemesanan = $stmt_pemesanan->get_result();

            if ($result_pemesanan->num_rows > 0):
                while ($row = $result_pemesanan->fetch_assoc()):
                    // Cari nama mobil berdasarkan id_mobil dari API
                    $mobil = array_filter($data_mobil['data'], function ($item) use ($row) {
                        return $item['id_mobil'] == $row['id_mobil'];
                    });

                    $mobil = array_shift($mobil); // Ambil elemen pertama yang cocok
                    $nama_mobil = $mobil ? $mobil['nama_mobil'] : 'Tidak ditemukan';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id_pemesanan']) ?></td>
                        <td><?= htmlspecialchars($nama_mobil) ?></td>
                        <td><?= htmlspecialchars($row['jumlah']) ?></td>
                        <td>Rp <?= number_format($row['total_harga'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($row['tanggal_pemesanan']) ?></td>
                        <td>
                            <a href="pembayaran.php?id_pemesanan=<?= htmlspecialchars($row['id_pemesanan']) ?>">Bayar</a>
                        </td>
                    </tr>
                    <?php
                endwhile;
            else:
                ?>
                <tr>
                    <td colspan="6">Tidak ada pemesanan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <!-- sini -->
    <h2>Form Helpdesk</h2>
    <form method="POST">
        <label for="judul">Judul:</label><br>
        <input type="text" name="judul" id="judul" required><br><br>

        <label for="deskripsi">Deskripsi:</label><br>
        <textarea name="deskripsi" id="deskripsi" required></textarea><br><br>

        <button type="submit">Kirim Tiket</button>
    </form>

    <br>
    <a href="logout.php">Logout</a>

    <?php

    // Fungsi untuk mengambil data helpdesk dari API
    function fetchHelpdeskData()
    {
        $apiUrl = 'http://10.200.128.220/tst/bis/crud_mobil/api_helpdesk.php';
        $response = @file_get_contents($apiUrl);

        if ($response === FALSE) {
            return false;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return false; // optional: you might want to log this error
        }

        return $data;
    }

    // Ambil data dari API
    $helpdeskData = fetchHelpdeskData();

    if ($helpdeskData && $helpdeskData['status'] === 200 && is_array($helpdeskData['data'])) {
        echo '<h2>Helpdesk</h2>';
        echo '<table border="1" cellspacing="0" cellpadding="10">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>ID Helpdesk</th>';
        echo '<th>Nama Pelanggan</th>';
        echo '<th>Judul</th>';
        echo '<th>Deskripsi</th>';
        echo '<th>Status</th>';
        echo '<th>Tanggal</th>';
        echo '<th>Balasan</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        // Sesuaikan dengan cara mendeteksi pengguna yang sedang login
        $currentUserId = htmlspecialchars($user['nama_pelanggan']) ; // ganti dengan cara yang sesuai untuk mendapatkan ID pengguna yang sedang login
        foreach ($helpdeskData['data'] as $row) {
            if ($row['nama_pelanggan'] == $currentUserId) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id_helpdesk']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_pelanggan']) . '</td>';
                echo '<td>' . htmlspecialchars($row['judul']) . '</td>';
                echo '<td>' . htmlspecialchars($row['deskripsi']) . '</td>';
                echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                echo '<td>' . htmlspecialchars($row['tanggal_buat']) . '</td>';
                echo '<td>' . ($row['balasan'] ? htmlspecialchars($row['balasan']) : 'Belum ada balasan') . '</td>';
                echo '<td><a href="respon_helpdesk.php?id=' . htmlspecialchars($row['id_helpdesk']) . '">Balas</a></td>';
                echo '</tr>';
            }
        }

        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No helpdesk data found</p>';
    }
    ?>
    
</body>

</html>