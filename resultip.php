<?php
// Koneksi ke database
$mysqli = new mysqli("localhost", "absenpkl_db", "t_p:X5NTz#w_]/dH", "absenpkl_db");

// Cek koneksi
if ($mysqli->connect_error) {
    die("Koneksi database gagal: " . $mysqli->connect_error);
}

// Inisialisasi variabel untuk pesan
$message = "";

// Cek apakah kelas IP dipilih
if (isset($_POST['ip_class'])) {
    $selected_class = $_POST['ip_class'];
    switch ($selected_class) {
        case 'Class A':
            $query = "SELECT domain, ip_address FROM ip_class_a";
            break;
        case 'Class B':
            $query = "SELECT domain, ip_address FROM ip_class_b";
            break;
        case 'Class C':
            $query = "SELECT domain, ip_address FROM ip_class_c";
            break;
        default:
            $message = "<p class='no-result'>Kelas IP tidak valid.</p>";
            $query = "";
    }

    // Ambil data dari tabel sesuai kelas IP
    if ($query != "") {
        $result = $mysqli->query($query);

        // Pesan jika tabel kosong
        if ($result->num_rows == 0) {
            $message = "<p class='no-result'>Tidak ada data domain dan IP di kelas $selected_class yang tersimpan.</p>";
        } else {
            $message = "<h2>Daftar Domain dan IP Address $selected_class</h2>";
            $message .= "<table class='result-table'>
                            <tr>
                                <th>No.</th>
                                <th>Domain</th>
                                <th>IP Address</th>
                            </tr>";

            // Variabel untuk nomor urut
            $no = 1;

            // Loop melalui hasil query dan tampilkan dalam tabel
            while ($row = $result->fetch_assoc()) {
                $message .= "<tr>
                                <td>{$no}</td>
                                <td>{$row['domain']}</td>
                                <td>{$row['ip_address']}</td>
                             </tr>";
                $no++;
            }
            $message .= "</table>";
        }
    }
} else {
    $message = "<p class='no-result'>Pilih kelas IP untuk menampilkan data.</p>";
}

// Tutup koneksi
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Domain dan IP Berdasarkan Kelas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e9f9e7;
            margin: 0;
            padding: 20px;
        }

        h1, h2 {
            color: #2f6627;
        }

        .no-result {
            color: #6c757d;
            font-style: italic;
            font-size: 18px;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        .result-table th {
            background-color: #28a745;
            color: white;
        }

        .result-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .result-table tr:hover {
            background-color: #f1f1f1;
        }

        .form-container {
            margin-bottom: 20px;
        }

        .select-ip-class {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #28a745;
            font-size: 16px;
        }

        .btn-container {
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn:hover {
            background-color: #218838;
        }

        .btn-submit {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
        }

        .btn-submit:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <h1>Pilih Kelas IP Address</h1>

    <!-- Form untuk memilih kelas IP -->
    <div class="form-container">
        <form action="resultip.php" method="POST">
            <label for="ip_class">Pilih kelas IP:</label>
            <select name="ip_class" id="ip_class" class="select-ip-class" required>
                <option value="" disabled selected>Pilih kelas IP</option>
                <option value="Class A">Class A</option>
                <option value="Class B">Class B</option>
                <option value="Class C">Class C</option>
            </select>
            <button type="submit" class="btn-submit">Tampilkan</button>
        </form>
    </div>

    <!-- Menampilkan pesan atau tabel -->
    <div><?php echo $message; ?></div>

    <!-- Tombol kembali ke halaman utama -->
    <div class="btn-container">
        <a href="cekip.php" class="btn">Kembali ke Cek IP</a>
    </div>
</body>
</html>

