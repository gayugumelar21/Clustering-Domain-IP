<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to the database
$mysqli = new mysqli("localhost", "absenpkl_db", "t_p:X5NTz#w_]/dH", "absenpkl_db");

// Check connection
if ($mysqli->connect_error) {
    die("Koneksi database gagal: " . $mysqli->connect_error);
}

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
    $target_dir = "domains/";
    $target_file = $target_dir . basename($_FILES["domain_file"]["name"]);
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($file_type != "txt") {
        $message = "<div class='error'>Harap unggah file dengan format .txt saja.</div>";
    } else {
        if (move_uploaded_file($_FILES["domain_file"]["tmp_name"], $target_file)) {
            $domains = file($target_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if ($domains) {
                $class_a = [];
                $class_b = [];
                $class_c = [];
                $invalid_domains = [];

                foreach ($domains as $domain) {
                    $ip_address = gethostbyname($domain);

                    if ($ip_address === $domain) {
                        $invalid_domains[] = $domain;
                    } else {
                        $ip_class = get_ip_class($ip_address);
                        
                        // Simpan ke tabel yang sesuai berdasarkan kelas IP
                        switch ($ip_class) {
                            case 'Class A':
                                $class_a[] = ['domain' => $domain, 'ip' => $ip_address];
                                $stmt = $mysqli->prepare("INSERT INTO ip_class_a (domain, ip_address) VALUES (?, ?)");
                                break;
                            case 'Class B':
                                $class_b[] = ['domain' => $domain, 'ip' => $ip_address];
                                $stmt = $mysqli->prepare("INSERT INTO ip_class_b (domain, ip_address) VALUES (?, ?)");
                                break;
                            case 'Class C':
                                $class_c[] = ['domain' => $domain, 'ip' => $ip_address];
                                $stmt = $mysqli->prepare("INSERT INTO ip_class_c (domain, ip_address) VALUES (?, ?)");
                                break;
                            default:
                                continue 2; // Lewati domain jika bukan Class A, B, atau C
                        }

                        // Eksekusi query simpan ke database
                        $stmt->bind_param("ss", $domain, $ip_address);
                        $stmt->execute();
                        $stmt->close();
                    }
                }

                // Alert selesai clustering
                $message .= "<div class='alert-box'>
                                <span class='close-btn'>&times;</span>
                                <strong>Selesai!</strong> Clustering IP selesai.
                             </div>";

                // Menampilkan hasil untuk setiap kelas
                $message .= "<h2>Hasil Pengelompokan IP Address:</h2>";

                if (!empty($class_a)) {
                    $message .= "<h3>Class A</h3><table class='result-table'>
                        <tr><th>Domain</th><th>IP Address</th></tr>";
                    foreach ($class_a as $entry) {
                        $message .= "<tr><td>{$entry['domain']}</td><td>{$entry['ip']}</td></tr>";
                    }
                    $message .= "</table>";
                }

                if (!empty($class_b)) {
                    $message .= "<h3>Class B</h3><table class='result-table'>
                        <tr><th>Domain</th><th>IP Address</th></tr>";
                    foreach ($class_b as $entry) {
                        $message .= "<tr><td>{$entry['domain']}</td><td>{$entry['ip']}</td></tr>";
                    }
                    $message .= "</table>";
                }

                if (!empty($class_c)) {
                    $message .= "<h3>Class C</h3><table class='result-table'>
                        <tr><th>Domain</th><th>IP Address</th></tr>";
                    foreach ($class_c as $entry) {
                        $message .= "<tr><td>{$entry['domain']}</td><td>{$entry['ip']}</td></tr>";
                    }
                    $message .= "</table>";
                }

                if (empty($class_a) && empty($class_b) && empty($class_c)) {
                    $message .= "<p class='no-result'>Tidak ada domain dengan IP Class A, B, atau C.</p>";
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
            background-color: #f4f7f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .upload-form {
            text-align: center;
        }
        .upload-form input[type="file"] {
            margin-bottom: 20px;
        }
        .upload-form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .upload-form button:hover {
            background-color: #45a049;
        }
        .alert-box {
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            margin-bottom: 20px;
            border-radius: 5px;
            position: relative;
        }
        .alert-box .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
        }
        .error {
            padding: 15px;
            background-color: #f44336;
            color: white;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .result-table th {
            background-color: #f2f2f2;
        }
        .result-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .result-table tr:hover {
            background-color: #f1f1f1;
        }
        .no-result {
            text-align: center;
            font-size: 18px;
            color: #666;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Unggah File .txt Berisi Daftar Domain</h1>

        <!-- Form untuk mengunggah file -->
        <div class="upload-form">
            <form action="cekip.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="domain_file" id="domain_file" accept=".txt" required>
                <br>
                <button type="submit">Unggah dan Cek IP Address</button>
            </form>
        </div>

        <!-- Menampilkan hasil -->
        <div><?php echo $message; ?></div>
    </div>
</body>
</html>

