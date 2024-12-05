<?php
header("Content-Type: application/json");
require_once 'koneksi.php';

// Fungsi untuk mengirim respons
function response($status, $message, $data = null) {
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Mendapatkan method dari request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Mendapatkan semua data mobil
        $sql = "SELECT * FROM mobil";
        $result = $conn->query($sql);

        $mobil = [];
        while ($row = $result->fetch_assoc()) {
            $mobil[] = $row;
        }

        response(200, "Data mobil berhasil diambil", $mobil);
        break;

    case 'PUT':
        // Memperbarui data mobil
        $input = json_decode(file_get_contents('php://input'), true);
        $id_mobil = $input['id_mobil'] ?? null;
        $nama_mobil = $input['nama_mobil'] ?? null;
        $merk_mobil = $input['merk_mobil'] ?? null;
        $bahan_bakar = $input['bahan_bakar'] ?? null;
        $tahun = $input['tahun'] ?? null;
        $jumlah = $input['jumlah'] ?? null;
        $harga = $input['harga'] ?? null;

        if (!$id_mobil || !$nama_mobil || !$merk_mobil || !$bahan_bakar || !$tahun || !$jumlah || !$harga) {
            response(400, "Semua data (id_mobil, nama_mobil, merk_mobil, bahan_bakar, tahun, jumlah, harga) harus diisi");
        }

        $sql = "UPDATE mobil 
                SET nama_mobil='$nama_mobil', merk_mobil='$merk_mobil', bahan_bakar='$bahan_bakar', 
                    tahun='$tahun', jumlah='$jumlah', harga='$harga' 
                WHERE id_mobil='$id_mobil'";
        if ($conn->query($sql) === TRUE) {
            response(200, "Data mobil berhasil diperbarui");
        } else {
            response(500, "Gagal memperbarui data mobil: " . $conn->error);
        }
        break;

    case 'DELETE':
        // Menghapus data mobil
        $input = json_decode(file_get_contents('php://input'), true);
        $id_mobil = $input['id_mobil'] ?? null;

        if (!$id_mobil) {
            response(400, "ID mobil harus diisi");
        }

        $sql = "DELETE FROM mobil WHERE id_mobil='$id_mobil'";
        if ($conn->query($sql) === TRUE) {
            response(200, "Data mobil berhasil dihapus");
        } else {
            response(500, "Gagal menghapus data mobil: " . $conn->error);
        }
        break;

    default:
        response(405, "Metode tidak diizinkan");
        break;
}

$conn->close();
?>
