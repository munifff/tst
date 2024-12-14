<?php
// Koneksi ke database manajemen_mobil
include 'koneksi.php';

// URL API SOAP untuk helpdesk
$soap_url = "http://10.200.128.220/tst/bis/crud_mobil/index.php";

// Membuat permintaan SOAP dalam format XML
$xml_request = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:help="http://example.com/helpdesk">
   <soapenv:Header/>
   <soapenv:Body>
      <help:GetHelpdesk>
         <!-- Jika API butuh parameter, tambahkan di sini -->
      </help:GetHelpdesk>
   </soapenv:Body>
</soapenv:Envelope>
XML;

// Inisialisasi cURL untuk permintaan SOAP
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $soap_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: text/xml; charset=utf-8",
    "Content-Length: " . strlen($xml_request)
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);

// Eksekusi cURL dan ambil respons
$response = curl_exec($ch);

if (curl_errno($ch)) {
    die("Error: " . curl_error($ch));
}

curl_close($ch);

// Parsing respons SOAP
$xml = simplexml_load_string($response);
$namespaces = $xml->getNamespaces(true);

// Menavigasi ke isi respons
$body = $xml->children($namespaces['soapenv'])->Body;
$data = $body->children($namespaces['help'])->GetHelpdeskResponse->HelpdeskList;

// Konversi ke array (opsional jika dibutuhkan array)
$helpdesk_list = json_decode(json_encode($data), true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helpdesk</title>
</head>
<body>
    <h1>Helpdesk</h1>
    <table border="1" cellspacing="0" cellpadding="10">
        <thead>
            <tr>
                <th>ID Helpdesk</th>
                <th>Nama Pelanggan</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($helpdesk_list)) {
                foreach ($helpdesk_list as $row) {
                    echo "<tr>
                        <td>{$row['id_helpdesk']}</td>
                        <td>{$row['nama_pelanggan']}</td>
                        <td>{$row['judul']}</td>
                        <td>{$row['deskripsi']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['tanggal_buat']}</td>
                        <td>
                            <a href='respon_helpdesk.php?id={$row['id_helpdesk']}'>Balas</a>
                        </td>
                      </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Tidak ada tiket helpdesk.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
                