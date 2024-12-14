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

        if(json_last_error() !== JSON_ERROR_NONE) {
            response(400,  "gagal", $input);
            exit;
        }

        $idMobil = $input['id_mobil'];
        $query = "SELECT * FROM mobil WHERE id_mobil='$idMobil' LIMIT 1";
        $result = $conn->query($query);
        $mobil = [];
        while ($row = $result->fetch_assoc()) {
            $mobil[] = $row;
        }

        $data = [
            'id_mobil' => $input['id_mobil'] ?? null,
            'nama_mobil' => $input['nama_mobil'] ?? $mobil[0]['nama_mobil'],
            'merk_mobil' => $input['merk_mobil'] ?? $mobil[0]['merk_mobil'],
            'bahan_bakar' => $input['bahan_bakar'] ?? $mobil[0]['bahan_bakar'],
            'tahun' => $input['tahun'] ?? $mobil[0]['tahun'],
            'jumlah' => $input['stok'] ?? null, // Use stok for input value
            'harga' => $input['harga'] ?? $mobil[0]['harga']
        ];
        
        // Update the jumlah with the existing stock
        $data['jumlah'] = $mobil[0]['jumlah'] + $data['jumlah'];
        
        // Validate the required fields
        foreach ($data as $key => $value) {
            if (!isset($value)) {
                response(400, "Semua data ($key) harus diisi", $mobil[0]);
            }
        }
        
        // Update query
        $sql = "UPDATE mobil 
                SET nama_mobil = '{$data['nama_mobil']}', 
                    merk_mobil = '{$data['merk_mobil']}', 
                    bahan_bakar = '{$data['bahan_bakar']}', 
                    tahun = '{$data['tahun']}', 
                    jumlah = '{$data['jumlah']}', 
                    harga = '{$data['harga']}' 
                WHERE id_mobil = '{$data['id_mobil']}'";
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
