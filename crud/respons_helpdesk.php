<?php
include 'koneksi.php';

// Pastikan ada parameter id pada URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID Helpdesk tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$id_helpdesk = intval($_GET['id']); // Validasi ID
$sql_helpdesk = "SELECT h.id_helpdesk, p.nama_pelanggan, h.judul, h.deskripsi, h.status
                 FROM helpdesk h
                 JOIN manajemen_user.pelanggan p ON h.id_pelanggan = p.id_pelanggan
                 WHERE h.id_helpdesk = $id_helpdesk";
$result_helpdesk = $conn->query($sql_helpdesk);

if ($result_helpdesk->num_rows == 0) {
    echo "<script>alert('Tiket helpdesk tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$data = $result_helpdesk->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $respon = $_POST['respon'];

    // **Simpan respons ke database manajemen_user**
    $sql_respon = "INSERT INTO manajemen_user.respon_helpdesk (id_helpdesk, respon) 
                   VALUES ($id_helpdesk, '$respon')";

    if ($conn->query($sql_respon) === TRUE) {
        // Update status tiket helpdesk menjadi 'closed'
        $conn->query("UPDATE helpdesk SET status = 'closed' WHERE id_helpdesk = $id_helpdesk");
        echo "<script>alert('Respons berhasil disimpan!'); window.location='index.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan respons: {$conn->error}');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balas Helpdesk</title>
</head>
<body>
    <h1>Balas Tiket Helpdesk</h1>
    <h2>Detail Tiket</h2>
    <p><strong>ID Tiket:</strong> <?= $data['id_helpdesk'] ?></p>
    <p><strong>Nama Pelanggan:</strong> <?= $data['nama_pelanggan'] ?></p>
    <p><strong>Judul:</strong> <?= $data['judul'] ?></p>
    <p><strong>Deskripsi:</strong> <?= $data['deskripsi'] ?></p>
    <p><strong>Status:</strong> <?= $data['status'] ?></p>

    <h2>Form Respons</h2>
    <form method="POST">
        <textarea name="respon" rows="5" cols="50" placeholder="Tulis respons di sini..." required></textarea><br><br>
        <button type="submit">Kirim Respons</button>
    </form>

    <br>
    <a href="index.php">Kembali ke Halaman Utama</a>
</body>
</html>
