<?php
session_start();

// Mengecek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // ID pengguna dari sesi

// Mengecek apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['judul']) && isset($_POST['deskripsi'])) {
        $judul = htmlspecialchars(trim($_POST['judul']));
        $deskripsi = htmlspecialchars(trim($_POST['deskripsi']));

        // Mengirimkan tiket helpdesk melalui SOAP API
        try {
            $soapClient = new SoapClient("http://10.200.128.220/tst/bis/crud_mobil/soap_helpdesk.php?wsdl", array('trace' => 1)); // Ganti dengan URL WSDL yang sesuai

            // Menyiapkan parameter untuk SOAP
            $params = array(
                'id_pelanggan' => $user_id,
                'judul' => $judul,
                'deskripsi' => $deskripsi
            );

            // Memanggil fungsi SOAP untuk mengirim tiket
            $result = $soapClient->createHelpdeskTicket($params);

            // Memeriksa respon dari SOAP server
            if ($result->status == 'success') {
                $alert_message = "Tiket Helpdesk berhasil dikirim!";
                echo "<script>alert('$alert_message'); window.location='dashboard.php';</script>";
            } else {
                $alert_message = "Gagal mengirim tiket helpdesk: " . $result->message;
                echo "<script>alert('$alert_message'); window.location='dashboard.php';</script>";
            }
        } catch (SoapFault $fault) {
            $alert_message = "Terjadi kesalahan saat mengirim tiket: " . $fault->getMessage();
            echo "<script>alert('$alert_message'); window.location='dashboard.php';</script>";
        }
    } else {
        $alert_message = "Judul dan deskripsi tidak boleh kosong.";
        echo "<script>alert('$alert_message'); window.location='dashboard.php';</script>";
    }
}
?>
