<?php
include '../../includes/koneksi.php';
session_start();

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Memeriksa apakah parameter ID sudah diterima
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// Mengambil ID dari parameter GET
$id = $_GET['id'];

// Menghapus data hutang dari database
$sqlDelete = "DELETE FROM data WHERE id = :id AND user_id = :user_id";
$stmtDelete = $conn->prepare($sqlDelete);
$stmtDelete->execute(['id' => $id, 'user_id' => $_SESSION['user_id']]);

// Redirect kembali ke halaman utama setelah penghapusan
header("Location: index.php");
exit();
?>
