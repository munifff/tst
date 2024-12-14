<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_mobil = $_POST['nama_mobil'];
    $merk_mobil = $_POST['merk_mobil'];
    $bahan_bakar = $_POST['bahan_bakar'];
    $tahun = $_POST['tahun'];
    $jumlah = $_POST['jumlah'];
    $harga = $_POST['harga'];

    $sql = "INSERT INTO mobil (nama_mobil, merk_mobil, bahan_bakar, tahun, jumlah, harga)
            VALUES ('$nama_mobil', '$merk_mobil', '$bahan_bakar', '$tahun', '$jumlah', '$harga')";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Mobil</title>
</head>
<body>
    <h1>Tambah Data Mobil</h1>
    <form method="POST">
        <label>Nama Mobil:</label><br>
        <input type="text" name="nama_mobil" required><br>
        <label>Merk Mobil:</label><br>
        <input type="text" name="merk_mobil" required><br>
        <label>Bahan Bakar:</label><br>
        <input type="text" name="bahan_bakar" required><br>
        <label>Tahun:</label><br>
        <input type="number" name="tahun" required><br>
        <label>Jumlah:</label><br>
        <input type="number" name="jumlah" required><br>
        <label>Harga:</label><br>
        <input type="number" name="harga" required><br><br>
        <button type="submit">Simpan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
