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

    // Persiapkan data untuk dikirim ke API
    $postData = [
        'user_id'   => $user_id,
        'judul'     => $judul,
        'deskripsi' => $deskripsi
    ];

    // Set up cURL untuk mengirim data ke API
    $ch = curl_init('http://10.200.128.220/TST/bis/crud_mobil/helpdesk.php'); // Ganti dengan URL API yang sesuai
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

    // Eksekusi cURL dan mendapatkan hasilnya
    $response = curl_exec($ch);

    // Cek jika cURL gagal
    if (curl_errno($ch)) {
        echo "<script>alert('Gagal mengirim tiket: " . curl_error($ch) . "');</script>";
        curl_close($ch);
        exit; // Hentikan eksekusi jika ada kesalahan
    }

    // Menutup koneksi cURL setelah selesai
    curl_close($ch);

    // Pastikan respons bukan null atau false
    if ($response !== false) {
        // Decode respons JSON
        $response_data = json_decode($response, true);

        // Cek apakah JSON berhasil didecode dan ada data yang diharapkan
        if (isset($response_data['status']) && $response_data['status'] === 'success') {
            echo "<script>alert('Tiket Helpdesk berhasil dikirim!'); window.location='dashboard.php';</script>";
        } else {
            // Cek jika status tidak berhasil atau respons tidak sesuai harapan
            $error_message = isset($response_data['message']) ? $response_data['message'] : 'Gagal mengirim tiket. Coba lagi nanti.';
            echo "<script>alert('$error_message');</script>";
        }
    } else {
        // Jika respons kosong atau null
        echo "<script>alert('Tidak ada respons dari server.');</script>";
    }
}
?>
