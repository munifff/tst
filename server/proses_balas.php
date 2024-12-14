<?php
// Koneksi ke database
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_helpdesk = isset($_POST['id_helpdesk']) ? $_POST['id_helpdesk'] : null;
    $balasan = isset($_POST['balasan']) ? $_POST['balasan'] : '';

    if ($id_helpdesk && $balasan) {
        // Simpan balasan ke database
        $query = $conn->prepare("UPDATE helpdesk SET balasan = ?, status = 'terbalas' WHERE id_helpdesk = ?");
        $query->bind_param("si", $balasan, $id_helpdesk);


        if ($query->execute()) {
            echo "Balasan berhasil dikirim.";
            echo "<a href='index.php'>Kembali ke daftar helpdesk</a>";
        } else {
            echo "Terjadi kesalahan saat mengirim balasan.";
        }
    } else {
        echo "ID helpdesk atau balasan tidak valid.";
    }
} else {
    echo "Metode tidak diizinkan.";
}
