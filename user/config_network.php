<?php
// Fungsi untuk mengatur DNS di Windows menggunakan PowerShell
function setDNSConfigWindows($interface, $dns1, $dns2) {
    // PowerShell command untuk mengatur DNS
    $command = 'powershell.exe -Command "Set-DnsClientServerAddress -InterfaceAlias \'' . $interface . '\' -ServerAddresses (\'' . $dns1 . '\', \'' . $dns2 . '\')"';
    
    // Eksekusi perintah dan tangkap output dan error-nya
    $output = shell_exec($command . " 2>&1");

    // Cek hasil eksekusi
    if ($output === null) {
        echo "Konfigurasi DNS berhasil diubah.<br>";
    } else {
        echo "Error saat mengubah konfigurasi DNS.<br>";
        echo "<pre>$output</pre>";
    }
}

// Masukkan parameter
$interface = "Ethernet"; // Ganti dengan nama interface yang sesuai
$dns1 = "8.8.8.8"; // DNS utama
$dns2 = "8.8.4.4"; // DNS sekunder

// Panggil fungsi untuk mengatur DNS
setDNSConfigWindows($interface, $dns1, $dns2);
?>
