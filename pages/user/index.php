<?php
include '../../includes/koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Mendapatkan ID user yang sedang login
$userId = $_SESSION['user_id'];
$fullname = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : 'User';

// Mendapatkan bulan saat ini dan bulan berikutnya
$currentMonth = date("m");
$nextMonth = date("m", strtotime("+1 month"));

// Kueri untuk data hutang bulan ini, diurutkan berdasarkan tanggal ASC
$sqlCurrentMonth = "SELECT * FROM data WHERE user_id = :userId AND MONTH(date) = :currentMonth ORDER BY date ASC";
$stmtCurrentMonth = $conn->prepare($sqlCurrentMonth);
$stmtCurrentMonth->execute(['userId' => $userId, 'currentMonth' => $currentMonth]);
$resultCurrentMonth = $stmtCurrentMonth->fetchAll(PDO::FETCH_ASSOC);

// Kueri untuk data hutang bulan berikutnya, diurutkan berdasarkan tanggal ASC
$sqlNextMonth = "SELECT * FROM data WHERE user_id = :userId AND MONTH(date) = :nextMonth ORDER BY date ASC";
$stmtNextMonth = $conn->prepare($sqlNextMonth);
$stmtNextMonth->execute(['userId' => $userId, 'nextMonth' => $nextMonth]);
$resultNextMonth = $stmtNextMonth->fetchAll(PDO::FETCH_ASSOC);

$today = date("Y-m-d");
$totalAmount = 0;
$no = 1;

if (!isset($_SESSION['notif_shown'])) {
    $_SESSION['notif_shown'] = false;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Hutang</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../../assets/img/iconputih.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <a class="navbar-brand" href=""><img src="../../assets/img/iconputih.png" alt="Logo" width="30"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="all.php">Semua Data</a>
                </li>
            </ul>
            <style>
                .nav-link {
                    color: #fff !important;
                }

                .nav-item.bg-danger {
                    padding: 0 10px;
                    border-radius: 10px;
                }

                .nav-link:hover {
                    color: #ddd !important;
                }

                .edit {
                    border-radius: 10px;
                }

                .navbar-toggler-icon {
                    background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba%288, 8, 8, 1%29' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") !important;
                }
            </style>
            <ul class="navbar-nav ml-auto bg-danger edit">
                <li class="nav-item bg-danger">
                    <a class="nav-link text-white" href="../../logout.php" id="logoutLink">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <style>
        .nav-item-space {
            margin-left: 20px;
            /* Adjust the value as needed */
        }
    </style>

    <div class="container mt-4">
        <p style="font-style: italic;">Pembayaran yang sedang berlangsung <img src="../../assets/img/live.png" alt=""></p>
        <table id="debtTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Aplikasi</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($resultCurrentMonth) > 0) {
                    $hasDebts = false;
                    foreach ($resultCurrentMonth as $row) {
                        if ($row['status'] != 'Lunas') {
                            $hasDebts = true;
                            $formattedDate = date("d F Y", strtotime($row['date']));
                            $statusClass = 'status-hutang';

                            echo "<tr>";
                            echo "<td>$no</td>";
                            echo "<td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>Rp. " . number_format(intval($row['amount']), 0, ',', '.') . "</td>";
                            echo "<td>" . $formattedDate . "</td>";
                            echo "<td class='$statusClass'>" . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . "</td>";
                            echo "<td>";
                            echo '<a href="#" data-id="' . $row['id'] . '" class="edit-btn"><i class="fas fa-edit"></i></a>';
                            echo '<a href="delete_debt.php?id=' . $row['id'] . '" class="delete-btn" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="fas fa-trash-alt"></i></a>';
                            echo "</td>";
                            echo "</tr>";

                            $totalAmount += $row['amount'];
                            $no++;
                        }
                    }
                    if (!$hasDebts) {
                        echo "<tr><td colspan='6'>Tidak ada hutang pada bulan ini 🥳!</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Tidak ada hutang pada bulan ini 🥳!</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr style="background-color:gold; font-weight: bold; text-align: center;">
                    <th colspan="5" style="text-align: right;">Total:</th>
                    <td>Rp. <?php echo number_format($totalAmount, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <!--Akan datang-->
        <br>
        <p style="font-style: italic;">Pembayaran yang akan datang❗</p>
        <table id="debtTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Aplikasi</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalAmountNextMonth = 0;
                $no = 1;
                if (count($resultNextMonth) > 0) {
                    foreach ($resultNextMonth as $row) {
                        $formattedDate = date("d F Y", strtotime($row['date']));
                        $statusClass = ($row['status'] == 'Lunas') ? 'status-lunas' : 'status-hutang';

                        echo "<tr>";
                        echo "<td>$no</td>";
                        echo "<td>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>Rp. " . number_format(intval($row['amount']), 0, ',', '.') . "</td>";
                        echo "<td>" . $formattedDate . "</td>";
                        echo "<td class='$statusClass'>" . htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>";
                        echo '<a href="#" data-id="' . $row['id'] . '" class="edit-btn"><i class="fas fa-edit"></i></a>';
                        echo '<a href="delete_debt.php?id=' . $row['id'] . '" class="delete-btn" onclick="return confirm(\'Apakah Anda yakin ingin menghapus data ini?\')"><i class="fas fa-trash-alt"></i></a>';
                        echo "</td>";
                        echo "</tr>";
                        if ($row['status'] != 'Lunas') {
                            $totalAmountNextMonth += $row['amount'];
                        }
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='6'>Tidak ada hutang yang akan datang!</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr style="background-color:gold; font-weight: bold; text-align: center;">
                    <th colspan="5" style="text-align: right;">Total:</th>
                    <td>Rp. <?php echo number_format($totalAmountNextMonth, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <a href="#" class="btn btn-success btn-block" id="addDebtBtn">Tambah Hutang Baru</a>

    </div>

    <div id="addDebtModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tambah Hutang Baru</h2>
                <span class="close">&times;</span>
            </div>
            <form id="debtForm" action="add_debt.php" method="POST">
                <label for="name">Nama:</label>
                <input type="text" id="name" name="name" placeholder="Masukan Nama Aplikasi" required>
                <label for="amount">Jumlah:</label>
                <input type="number" id="amount" name="amount" placeholder="Masukan Jumlah Hutang" required>
                <label for="date">Tanggal:</label>
                <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Hutang">Hutang</option>
                    <option value="Lunas">Lunas</option>
                </select><br>
                <input class="btn btn-primary btn-block" type="submit" value="Tambah">
            </form>
        </div>
    </div>

    <div id="editDebtModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Status Hutang</h2>
                <span class="close">&times;</span>
            </div>
            <form id="editForm" action="edit_debt.php" method="POST">
                <input type="hidden" id="edit-id" name="id">
                <label for="edit-status">Status:</label>
                <select id="edit-status" name="status" required>
                    <option value="Hutang">Hutang</option>
                    <option value="Lunas">Lunas</option>
                </select><br>
                <input class="btn btn-primary btn-block" type="submit" value="Update">
            </form>
        </div>
    </div>
</div>

    <script src="../../js/script.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var addDebtBtn = document.getElementById("addDebtBtn");
        var addDebtModal = document.getElementById("addDebtModal");
        var editDebtModal = document.getElementById("editDebtModal");
        var span = document.getElementsByClassName("close");

        addDebtBtn.onclick = function() {
            addDebtModal.style.display = "block";
        }

        for (let i = 0; i < span.length; i++) {
            span[i].onclick = function() {
                addDebtModal.style.display = "none";
                editDebtModal.style.display = "none";
            }
        }

        window.onclick = function(event) {
            if (event.target == addDebtModal) {
                addDebtModal.style.display = "none";
            } else if (event.target == editDebtModal) {
                editDebtModal.style.display = "none";
            }
        }

        document.querySelectorAll('.edit-btn').forEach(item => {
            item.addEventListener('click', event => {
                var id = event.target.getAttribute('data-id');
                var status = event.target.parentElement.previousElementSibling.innerHTML;
                document.getElementById('edit-id').value = id;
                document.getElementById('edit-status').value = status;
                editDebtModal.style.display = "block";
            })
        })
        document.addEventListener("DOMContentLoaded", function() {
            <?php if (!$_SESSION['notif_shown']): ?>
            Swal.fire({
                title: 'Selamat datang, <?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>',
                text: 'Tetap semangat dalam membayar hutang yaa!',
                icon: 'info',
                confirmButtonText: 'OK'
            }).then(function() {
                <?php $_SESSION['notif_shown'] = true; ?>
            });
            <?php endif; ?>

            // Konfirmasi Logout
            document.getElementById('logoutLink').addEventListener('click', function(event) {
                event.preventDefault();
                Swal.fire({
                    title: 'Apakah Anda yakin ingin logout?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Logout',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '../../logout.php';
                    }
                });
            });
        });
    </script>
</body>

</html>