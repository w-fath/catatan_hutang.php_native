<?php
include '../../includes/koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Mendapatkan ID user yang sedang login
$userId = $_SESSION['user_id'];

// Kueri untuk data sesuai dengan user yang sedang login
$sqlAllData = "SELECT * FROM data WHERE user_id = :userId ORDER BY DATE_FORMAT(date, '%Y-%m-%d') ASC";
$stmtAllData = $conn->prepare($sqlAllData);
$stmtAllData->execute(['userId' => $userId]);
$resultAllData = $stmtAllData->fetchAll(PDO::FETCH_ASSOC);

$totalAmount = 0; // Variabel untuk menyimpan total jumlah hutang
$no = 1;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan Hutang</title>
    <link rel="stylesheet" href="../../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../../assets/img/iconputih.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .status-lunas {
            background-color: #5cb85c;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        .status-hutang {
            background-color: #d9534f;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .print-btn {
            margin: 20px 0;
            padding: 10px 0px;
            background-color: #4CAF50;
            border-radius: 10px;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 10;
            display: block;
            width: 100px;
            margin-left: auto;
            margin-right: auto;
        }

        .print-btn:hover {
            background-color: #45a049;
        }

        @media print {
            .print-btn {
                display: none;
            }

            .status-lunas,
            .status-hutang {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            body {
                margin: 0;
                padding: 0;
                font-size: 12px;
            }

            table {
                page-break-inside: auto;
                border: none;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
                border: none;
            }
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
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
                <li class="nav-item">
                    <a class="nav-link text-white" href="all_del.php">Hapus Semua Data</a>
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
                    <a class="nav-link text-white" href="../../logout.php">Logout</a>
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


    <style>
        .nav-item-space {
            margin-left: 20px;
            /* Adjust the value as needed */
        }
    </style>

    <div class="container">
        <h1>Catatan Hutang</h1>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Cari berdasarkan nama...">
            <i class="fas fa-search search-icon"></i>
        </div>
        <style>
            .search-container {
                position: relative;
                display: flex;
                align-items: center;
                width: 100%;
                margin: 20px 0;
            }

            #searchInput {
                width: 100%;
                padding: 10px;
                box-sizing: border-box;
                border: 2px solid #ccc;
                border-radius: 4px;
                padding-left: 40px;
                /* Tambahkan padding kiri untuk ikon */
            }

            .search-icon {
                position: absolute;
                left: 10px;
                color: #aaa;
                font-size: 20px;
            }
        </style>

        <script>
            document.getElementById('searchInput').addEventListener('keyup', function() {
                var input = document.getElementById('searchInput');
                var filter = input.value.toUpperCase();
                var table = document.getElementById('debtTable');
                var tr = table.getElementsByTagName('tr');
                var totalAmount = 0;
                var no = 1;

                for (var i = 1; i < tr.length; i++) {
                    var td = tr[i].getElementsByTagName('td')[1]; // kolom nama
                    if (td) {
                        var txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            tr[i].getElementsByTagName('td')[0].textContent = no++; // Mengatur nomor urut
                            var amountTd = tr[i].getElementsByTagName('td')[2]; // kolom jumlah
                            var amountValue = parseInt(amountTd.textContent.replace(/[^0-9]/g, ''));
                            if (!isNaN(amountValue) && tr[i].getElementsByTagName('td')[4].className === 'status-hutang') {
                                totalAmount += amountValue;
                            }
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }

                document.getElementById('totalAmount').textContent = 'Rp. ' + totalAmount.toLocaleString('id-ID');
            });
        </script>

        <table id="debtTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Aplikasi</th>
                    <th>Jumlah</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (count($resultAllData) > 0) {
                    foreach ($resultAllData as $row) {
                        // Validasi dan sanitasi data sebelum ditampilkan
                        $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                        $amount = $row['amount'];
                        $formattedAmount = number_format($amount, 0, ',', '.');
                        $formattedDate = date("d F Y", strtotime($row['date']));
                        $status = htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8');
                        $statusClass = ($status == 'Lunas') ? 'status-lunas' : 'status-hutang';

                        echo "<tr>";
                        echo "<td>$no</td>";
                        echo "<td>$name</td>";
                        echo "<td>Rp. $formattedAmount</td>";
                        echo "<td>$formattedDate</td>";
                        echo "<td class='$statusClass'>$status</td>";
                        echo "</tr>";

                        // Menambahkan jumlah hutang ke totalAmount hanya jika statusnya "Hutang"
                        if ($status == 'Hutang') {
                            $totalAmount += $amount;
                        }
                        $no++;
                    }
                } else {
                    echo "<tr><td colspan='5'>Tidak ada data hutang</td></tr>";
                }
                ?>
            </tbody>
            <tfoot>
                <tr style="background-color:gold; font-weight: bold; text-align: center;">
                    <th colspan="4" style="text-align: right;">Total:</th>
                    <td id="totalAmount">Rp. <?php echo number_format($totalAmount, 0, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>

        <!-- Tombol untuk mencetak halaman -->
        <button class="print-btn" onclick="printPage()">
            <i class="fas fa-print print-icon"></i> Cetak
        </button>
    </div>
</body>

</html>
