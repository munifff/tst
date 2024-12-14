<?php
// Koneksi ke database
include 'koneksi.php';

// Mendapatkan ID dari URL
$id_helpdesk = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_helpdesk) {
    echo "ID helpdesk tidak ditemukan.";
    exit;
}

// Query untuk mendapatkan data helpdesk berdasarkan ID
$query = $conn->prepare("SELECT * FROM helpdesk WHERE id_helpdesk = ?");
$query->bind_param("i", $id_helpdesk);
$query->execute();
$result = $query->get_result();

$data = $result->fetch_assoc();

if (!$data) {
    echo "Data helpdesk tidak ditemukan.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balas Helpdesk</title>
</head>
<body>
    <h2>Balas Helpdesk</h2>
    <form action="proses_balas.php" method="POST">
        <input type="hidden" name="id_helpdesk" value="<?php echo htmlspecialchars($data['id_helpdesk']); ?>">
        
        <p>
            <strong>Judul:</strong> <?php echo htmlspecialchars($data['judul']); ?><br>
            <strong>Deskripsi:</strong> <?php echo htmlspecialchars($data['deskripsi']); ?><br>
        </p>
        
        <label for="balasan">Balasan:</label><br>
        <textarea name="balasan" id="balasan" cols="30" rows="5" required></textarea><br><br>
        
        <button type="submit">Kirim Balasan</button>
    </form>
</body>
</html>
