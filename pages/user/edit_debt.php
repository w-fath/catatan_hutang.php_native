<?php
include '../../includes/koneksi.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $status = $_POST['status'];

    if (empty($id) || empty($status)) {
        echo json_encode(array('success' => false, 'message' => 'ID atau status tidak boleh kosong.'));
        exit();
    }

    $sql = "UPDATE data SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(array('success' => true, 'message' => 'Status hutang berhasil diperbarui'));
    } else {
        echo json_encode(array('success' => false, 'message' => 'Error: ' . $stmt->errorInfo()[2]));
    }
} else {
    echo json_encode(array('success' => false, 'message' => 'Request method tidak valid.'));
}

$conn = null;
?>
