<?php
include 'koneksi.php'; // Pastikan file koneksi di-include

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $nomer_telephone = $_POST['nomer_telephone'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    // Gunakan koneksi ke database manajemen_user
    $sql = "INSERT INTO pelanggan (nama_pelanggan, jenis_kelamin, nomer_telephone, username, password) 
            VALUES ('$nama', '$jenis_kelamin', '$nomer_telephone', '$username', '$password')";

    if ($conn_user->query($sql) === TRUE) {
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
    } else {
        echo "<script>alert('Registrasi gagal: {$conn_user->error}');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>
    <form method="POST">
        <label>Nama:</label><br>
        <input type="text" name="nama" required><br>
        <label>Jenis Kelamin:</label><br>
        <select name="jenis_kelamin" required>
            <option value="Laki-laki">Laki-laki</option>
            <option value="Perempuan">Perempuan</option>
        </select><br>
        <label>Nomor Telepon:</label><br>
        <input type="text" name="nomer_telephone" required><br>
        <label>Username:</label><br>
        <input type="text" name="username" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Register</button>
        <a href="login.php">Sudah punya akun? Login</a>
    </form>
</body>
</html>
