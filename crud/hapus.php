<?php
include 'koneksi.php';

$id = $_GET['id'];
$sql = "DELETE FROM mobil WHERE id_mobil = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
