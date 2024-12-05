<?php
session_start();
include 'koneksi.php';  // Pastikan koneksi.php yang digunakan adalah koneksi untuk manajemen_mobil

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // ID pengguna dari sesi

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];

    // Simpan tiket helpdesk ke dalam database manajemen_mobil
    $sql_helpdesk = "INSERT INTO helpdesk (id_pelanggan, judul, deskripsi)
                     VALUES ($user_id, '$judul', '$deskripsi')";

    // Gunakan koneksi ke database manajemen_mobil
    if ($conn_mobil->query($sql_helpdesk) === TRUE) {
        echo "<script>alert('Tiket Helpdesk berhasil dikirim!'); window.location='dashboard.php';</script>";
    } else {
        echo "<script>alert('Gagal mengirim tiket: {$conn_mobil->error}');</script>";
    }
}
?>
