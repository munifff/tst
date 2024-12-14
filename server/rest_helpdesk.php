<?php
// Include koneksi database
include_once('koneksi.php');

// Menangani request GET untuk mengambil data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Perbarui status berdasarkan isi kolom balasan
    $conn->query("UPDATE helpdesk SET status = IF(balasan IS NOT NULL AND balasan != '', 'terbalas', 'open')");

    // Query untuk mengambil data helpdesk
    $query = "SELECT * FROM helpdesk";
    $result = $conn->query($query);

    // Array untuk menampung data helpdesk
    $data = [];

    // Cek apakah ada data
    if ($result->num_rows > 0) {
        // Loop melalui setiap baris data dan tambahkan ke array
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id_helpdesk' => $row['id_helpdesk'],
                'nama_pelanggan' => $row['nama_pelanggan'],
                'judul' => htmlspecialchars($row['judul']),
                'deskripsi' => htmlspecialchars($row['deskripsi']),
                'tanggal_buat' => $row['tanggal_buat'],
                'status' => $row['status'], // Status sudah diperbarui sesuai balasan
                'balasan' => htmlspecialchars($row['balasan'] ?? '') // Tampilkan balasan jika ada
            ];
        }

        // Output data dalam format JSON
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 200,
            'message' => 'Data retrieved successfully',
            'data' => $data
        ]);
    } else {
        // Jika tidak ada data, tampilkan pesan error
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 404,
            'message' => 'No helpdesk data found',
            'data' => []
        ]);
    }
}

// Menangani request POST untuk menambahkan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mengambil input JSON dari body
    $input = json_decode(file_get_contents('php://input'), true);

    // Validasi data input
    if (isset($input['nama_pelanggan']) && isset($input['judul']) && isset($input['deskripsi'])) {
        // Proses untuk menyimpan data ke database
        $id_pelanggan = $conn->real_escape_string($input['nama_pelanggan']);
        $judul = $conn->real_escape_string($input['judul']);
        $deskripsi = $conn->real_escape_string($input['deskripsi']);
        $tanggal_buat = date('Y-m-d H:i:s'); // Tanggal sekarang
        $status = 'open'; // Status default

        // Menyimpan data ke database
        $query = "INSERT INTO helpdesk (nama_pelanggan, judul, deskripsi, tanggal_buat, status) 
                  VALUES ('$id_pelanggan', '$judul', '$deskripsi', '$tanggal_buat', '$status')";
        $result = $conn->query($query);

        if ($result) {
            // Berhasil menambahkan tiket
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 200,
                'message' => 'Tiket berhasil ditambahkan.'
            ]);
        } else {
            // Gagal menyimpan data
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 500,
                'message' => 'Gagal menambahkan tiket. ' . $conn->error
            ]);
        }
    } else {
        // Data input tidak lengkap
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 400,
            'message' => 'Data tidak lengkap.'
        ]);
    }
}

// Menutup koneksi database
$conn->close();
