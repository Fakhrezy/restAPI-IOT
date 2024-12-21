<?php
// Koneksi ke database MySQL
$servername = "localhost";
$username = "root";  
$password = "";      
$dbname = "db_iot_tb";  

// Membuat koneksi ke MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// URL Firebase Realtime Database Anda
$firebaseUrl = 'https://smartgarden-28150-default-rtdb.firebaseio.com/sensors.json';  

// Mengambil data dari Firebase Realtime Database menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $firebaseUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$response = curl_exec($ch);
curl_close($ch);

// Mengecek apakah permintaan berhasil
if ($response === false) {
    echo 'Error: ' . curl_error($ch);
    exit();
}

// Mengubah JSON response menjadi array PHP
$data = json_decode($response, true);

// Proses untuk mengubah timestamp di dalam data JSON
foreach ($data as $sensorId => &$sensor) {
    // Timestamp dari data
    $timestamp = $sensor['timestamp'];

    // Cek apakah timestamp ada dan valid
    if (!empty($timestamp)) {
        // Mengganti 'T' dengan spasi dan menghapus zona waktu (+00:00) dengan bagian setelah tanda '+'
        $timestamp = str_replace('T', ' ', $timestamp);
        $timestamp = preg_replace('/\+.*$/', '', $timestamp);
        
        // Validasi format timestamp
        $timestampFormat = DateTime::createFromFormat('Y-m-d H:i:s', $timestamp);
        if ($timestampFormat === false) {
            // Timestamp tidak valid, tampilkan pesan error
            echo "Invalid timestamp format for Sensor ID $sensorId: $timestamp<br>";
            continue;  // Lewati sensor ini jika timestamp tidak valid
        }
        
        // Mengupdate timestamp yang sudah diproses kembali ke dalam array
        $sensor['timestamp'] = $timestamp;
    } else {
        // Jika timestamp kosong, tampilkan pesan error
        echo "Timestamp is empty for Sensor ID $sensorId<br>";
        continue;
    }
}

// Menampilkan seluruh data dengan format JSON yang terstruktur
header('Content-Type: application/json'); // Memberi tahu browser bahwa ini adalah output JSON
echo json_encode(array("sensors" => $data), JSON_PRETTY_PRINT);

// Menyimpan data ke MySQL jika data dengan ID sensor yang sama belum ada
foreach ($data as $sensorId => $sensor) {  // Perhatikan perubahan disini, $sensorId sudah diambil dari key Firebase
    // Query untuk memeriksa apakah ID sensor sudah ada di database
    $sql_check = "SELECT COUNT(*) FROM sensor_data WHERE sensor_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $sensorId);  // Gunakan $sensorId sebagai parameter
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    // Jika data dengan ID sensor belum ada di database, maka simpan data terbaru
    if ($count == 0) {
        // Ambil data dari sensor
        $temperature1 = $sensor['ESP1']['temperature-1'];
        $turbidity1 = $sensor['ESP1']['turbidity-1'];

        // Data dari ESP2
        $temperature2 = $sensor['ESP2']['temperature-2'];
        $turbidity2 = $sensor['ESP2']['turbidity-2'];

        // Timestamp dari data (sudah diproses)
        $timestamp = $sensor['timestamp'];

        // Query SQL untuk memasukkan data ke tabel sensor_data
        $sql = "INSERT INTO sensor_data (sensor_id, temperature1, turbidity1, temperature2, turbidity2)
                VALUES (?, ?, ?, ?, ?)";

        // Menyiapkan statement SQL
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing the SQL statement: " . $conn->error);
        }

        // Bind parameters dan eksekusi query
        $stmt->bind_param("sssss", $sensorId, $temperature1, $turbidity1, $temperature2, $turbidity2);
        $stmt->execute();

        // DEBUG POSTING DATA :
        // if ($stmt->affected_rows > 0) {
        //     echo "Data berhasil disimpan untuk Sensor ID: $sensorId<br>";
        // } else {
        //     echo "Gagal menyimpan data untuk Sensor ID: $sensorId<br>";
        // }

        // Menutup statement
        $stmt->close();
    } 
    // else {
            // Cek redudansi data
    //     echo "Data dengan Sensor ID $sensorId sudah ada di database, tidak perlu disimpan lagi.<br>";
    // }
}

// Menutup koneksi MySQL
$conn->close();
?>
