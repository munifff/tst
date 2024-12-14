<?php
// Koneksi ke database manajemen_user
$host_user = "localhost";
$user_user = "root";
$pass_user = "";
$db_user   = "manajemen_user";

$conn_user = new mysqli($host_user, $user_user, $pass_user, $db_user);

if ($conn_user->connect_error) {
    die("Koneksi ke database manajemen_user gagal: " . $conn_user->connect_error);
}

// Koneksi ke database manajemen_mobil
// $host_mobil = "localhost";
// $user_mobil = "root";
// $pass_mobil = "";
// $db_mobil   = "manajemen_mobil";

// $conn_mobil = new mysqli($host_mobil, $user_mobil, $pass_mobil, $db_mobil);

// if ($conn_mobil->connect_error) {
//     die("Koneksi ke database manajemen_mobil gagal: " . $conn_mobil->connect_error);
// }
?>
