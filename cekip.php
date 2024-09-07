<?php
// Inisialisasi variabel untuk pesan
$message = "";

// Fungsi untuk menentukan kelas IP
function get_ip_class($ip_address) {
    $first_octet = (int)explode('.', $ip_address)[0];

    if ($first_octet >= 1 && $first_octet <= 126) {
        return 'Class A';
    } elseif ($first_octet >= 128 && $first_octet <= 191) {
        return 'Class B';
    } elseif ($first_octet >= 192 && $first_octet <= 223) {
        return 'Class C';
    } else {
        return 'Lainnya'; // Di luar kelas A, B, C
    }
}

// Cek apakah file telah diunggah
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['domain_file'])) {
    // Mendefinisikan direktori tempat file akan diunggah sementara
    $target_dir = "domains/";
    $target_file = $target_dir . basename($_FILES["domain_file"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Pastikan file yang diunggah adalah file .txt
    if ($file_type != "txt") {
        $message = "<div class='error'>Harap unggah file dengan format .txt saja.</div>";
    } else {
        // Pindahkan file yang diunggah ke folder uploads
        if (move_uploaded_file($_FILES["domain_file"]["tmp_name"], $target_file)) {
            // Baca file dan simpan domain dalam array
            $domains = file($target_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($domains) {
                // Inisialisasi array untuk pengelompokan berdasarkan kelas
                $class_a = [];
                $class_b = [];
                $class_c = [];
                $invalid_domains = [];

                // Loop melalui setiap domain untuk mendapatkan IP address
                foreach ($domains as $domain) {
                    // Mengambil IP address dari domain
                    $ip_address = gethostbyname($domain);

                    // Cek apakah domain valid (IP address berubah setelah gethostbyname)
                    if ($ip_address === $domain) {
                        $invalid_domains[] = $domain;
                    } else {
                        // Tentukan kelas IP dan masukkan ke dalam grup yang sesuai
                        $ip_class = get_ip_class($ip_address);
                        if ($ip_class === 'Class A') {
                            $class_a[] = ['domain' => $domain, 'ip' => $ip_address];
                        } elseif ($ip_class === 'Class B') {
                            $class_b[] = ['domain' => $domain, 'ip' => $ip_address];
                        } elseif ($ip_class === 'Class C') {
                            $class_c[] = ['domain' => $domain, 'ip' => $ip_address];
                        }
                    }
                }

                // Tampilkan hasil pengelompokan dalam bentuk tabel
                $message .= "<h2>Hasil Pengelompokan IP Address Berdasarkan Kelas:</h2>";

                // Tampilkan IP Class A
                if (!empty($class_a)) {
                    $message .= "<h3>Class A:</h3>
                    <table class='result-table'>
                        <tr>
                            <th>Domain</th>
                            <th>IP Address</th>
                            <th>Kelas</th>
                        </tr>";
                    foreach ($class_a as $entry) {
                        $message .= "<tr>
                                        <td>{$entry['domain']}</td>
                                        <td>{$entry['ip']}</td>
                                        <td>Class A</td>
                                     </tr>";
                    }
                    $message .= "</table>";
                } else {
                    $message .= "<p class='no-result'>Tidak ada domain dengan IP Class A.</p>";
                }

                // Tampilkan IP Class B
                if (!empty($class_b)) {
                    $message .= "<h3>Class B:</h3>
                    <table class='result-table'>
                        <tr>
                            <th>Domain</th>
                            <th>IP Address</th>
                            <th>Kelas</th>
                        </tr>";
                    foreach ($class_b as $entry) {
                        $message .= "<tr>
                                        <td>{$entry['domain']}</td>
                                        <td>{$entry['ip']}</td>
                                        <td>Class B</td>
                                     </tr>";
                    }
                    $message .= "</table>";
                } else {
                    $message .= "<p class='no-result'>Tidak ada domain dengan IP Class B.</p>";
                }

                // Tampilkan IP Class C
                if (!empty($class_c)) {
                    $message .= "<h3>Class C:</h3>
                    <table class='result-table'>
                        <tr>
                            <th>Domain</th>
                            <th>IP Address</th>
                            <th>Kelas</th>
                        </tr>";
                    foreach ($class_c as $entry) {
                        $message .= "<tr>
                                        <td>{$entry['domain']}</td>
                                        <td>{$entry['ip']}</td>
                                        <td>Class C</td>
                                     </tr>";
                    }
                    $message .= "</table>";
                } else {
                    $message .= "<p class='no-result'>Tidak ada domain dengan IP Class C.</p>";
                }

                // Tampilkan domain yang tidak valid
                if (!empty($invalid_domains)) {
                    $message .= "<h3>Domain yang Tidak Valid atau Tidak Ditemukan:</h3>
                    <table class='result-table'>
                        <tr>
                            <th>Domain</th>
                            <th>Status</th>
                        </tr>";
                    foreach ($invalid_domains as $domain) {
                        $message .= "<tr>
                                        <td>$domain</td>
                                        <td>Domain tidak valid atau tidak ditemukan</td>
                                     </tr>";
                    }
                    $message .= "</table>";
                }

            } else {
                $message = "<div class='error'>File kosong atau tidak dapat dibaca.</div>";
            }
        } else {
            $message = "<div class='error'>Terjadi kesalahan saat mengunggah file.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek IP Address dari File Domain</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        h1, h2, h3 {
            color: #333;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }

        .no-result {
            color: #888;
            font-style: italic;
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
            background-color: #007bff;
            color: white;
        }

        .result-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .result-table tr:hover {
            background-color: #f1f1f1;
        }

        .upload-form {
            margin-bottom: 30px;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .upload-form label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }

        .upload-form input[type="file"] {
            display: block;
            margin-bottom: 15px;
        }

        .upload-form button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
        }

        .upload-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Unggah File .txt Berisi Daftar Domain</h1>

    <!-- Form untuk mengunggah file -->
    <div class="upload-form">
        <form action="cekip.php" method="POST" enctype="multipart/form-data">
            <label for="domain_file">Pilih file .txt yang berisi domain:</label>
            <input type="file" name="domain_file" id="domain_file" accept=".txt" required>
            <button type="submit">Unggah dan Cek IP Address</button>
        </form>
    </div>

    <!-- Menampilkan hasil -->
    <div><?php echo $message; ?></div>
</body>
</html>
