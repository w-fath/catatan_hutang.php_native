<?php
include '../../includes/koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    $name = htmlspecialchars($_POST['name']);
    $amount = intval($_POST['amount']);
    $date = $_POST['date'];
    $status = htmlspecialchars($_POST['status']);

    $sql = "INSERT INTO data (user_id, name, amount, date, status) VALUES (:user_id, :name, :amount, :date, :status)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute(['user_id' => $userId, 'name' => $name, 'amount' => $amount, 'date' => $date, 'status' => $status])) {
        $_SESSION['message'] = "Hutang berhasil ditambahkan!";
    } else {
        $_SESSION['message'] = "Terjadi kesalahan saat menambahkan hutang.";
    }
    header("Location: index.php");
    exit();
} else {
    $_SESSION['message'] = "Metode request tidak valid.";
    header("Location: index.php");
    exit();
}
?>
