<?php
include 'koneksi.php';

$id = $_GET['id'];
$sql = "SELECT * FROM mobil WHERE id_mobil = $id";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_mobil = $_POST['nama_mobil'];
    $merk_mobil = $_POST['merk_mobil'];
    $bahan_bakar = $_POST['bahan_bakar'];
    $tahun = $_POST['tahun'];
    $jumlah = $_POST['jumlah'];
    $harga = $_POST['harga'];

    $sql = "UPDATE mobil 
            SET nama_mobil='$nama_mobil', merk_mobil='$merk_mobil', bahan_bakar='$bahan_bakar', 
                tahun='$tahun', jumlah='$jumlah', harga='$harga'
            WHERE id_mobil=$id";

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
    <title>Edit Mobil</title>
</head>
<body>
    <h1>Edit Data Mobil</h1>
    <form method="POST">
        <label>Nama Mobil:</label><br>
        <input type="text" name="nama_mobil" value="<?= $data['nama_mobil'] ?>" required><br>
        <label>Merk Mobil:</label><br>
        <input type="text" name="merk_mobil" value="<?= $data['merk_mobil'] ?>" required><br>
        <label>Bahan Bakar:</label><br>
        <input type="text" name="bahan_bakar" value="<?= $data['bahan_bakar'] ?>" required><br>
        <label>Tahun:</label><br>
        <input type="number" name="tahun" value="<?= $data['tahun'] ?>" required><br>
        <label>Jumlah:</label><br>
        <input type="number" name="jumlah" value="<?= $data['jumlah'] ?>" required><br>
        <label>Harga:</label><br>
        <input type="number" name="harga" value="<?= $data['harga'] ?>" required><br><br>
        <button type="submit">Simpan</button>
        <a href="index.php">Kembali</a>
    </form>
</body>
</html>
