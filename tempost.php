<?php
// Sertakan file koneksi untuk menghubungkan ke database
include('koneksi.php');

// Firebase API URL
$firebaseUrl = 'https://flutter-firebase-app-772dd-default-rtdb.firebaseio.com/'; // Ganti <your-project-id> dengan ID proyek Firebase Anda

// Header untuk mengatur respons JSON
header("Content-Type: application/json");

// Periksa apakah ada parameter "action" di URL
if (isset($_GET['action']) && $_GET['action'] == 'store') {
    // Ambil data JSON yang dikirimkan melalui POST
    $data = json_decode(file_get_contents("php://input"), true);

    // Periksa apakah data berhasil diterima
    if ($data && isset($data["sensors"]) && count($data["sensors"]) > 0) {
        $sensorData = $data["sensors"][0]; // Mengakses sensor pertama dalam array

        // Validasi apakah data yang dibutuhkan ada
        if (isset($sensorData["ESP1"]["temperature-1"], $sensorData["ESP1"]["turbidity-1"], $sensorData["ESP1"]["voltage"], $sensorData["timestamp"])) {

            // Ambil nilai dari JSON
            $temperature1 = $sensorData["ESP1"]["temperature-1"];
            $turbidity1 = $sensorData["ESP1"]["turbidity-1"];
            $voltage = $sensorData["ESP1"]["voltage"];
            $timestamp = $sensorData["timestamp"];  // Mengambil timestamp dari sensor data

            // Query untuk menyimpan data ke MySQL dengan prepared statements
            $stmt = $conn->prepare("INSERT INTO sensor_data (temperature1, turbidity1, voltage, timestamp) 
                                    VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ddds", $temperature1, $turbidity1, $voltage, $timestamp); // Mengikat parameter

            // Eksekusi query
            if ($stmt->execute()) {
                // Kirim data ke Firebase menggunakan cURL
                $firebaseData = array(
                    'ESP1' => array(
                        'temperature-1' => $temperature1,
                        'turbidity-1' => $turbidity1,
                        'voltage' => $voltage
                    ),
                    'timestamp' => $timestamp // Menggunakan timestamp yang ada dalam data JSON
                );

                // Gunakan push untuk menambahkan data baru di Firebase
                $firebaseUrlWithPush = $firebaseUrl . '/sensors.json'; // Menambahkan '.json' untuk memungkinkan push
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $firebaseUrlWithPush);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($firebaseData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json'
                ));

                // Eksekusi cURL dan simpan respons
                $response = curl_exec($ch);
                curl_close($ch);

                // Cek respons dari Firebase
                if ($response) {
                    $decodedResponse = json_decode($response, true);
                    if (isset($decodedResponse["name"])) {
                        echo json_encode(["message" => "Data berhasil disimpan ke MySQL dan Firebase", "firebase_id" => $decodedResponse["name"]]);
                    } else {
                        echo json_encode(["error" => "Gagal menyimpan data ke Firebase"]);
                    }
                } else {
                    echo json_encode(["error" => "Gagal mengirim data ke Firebase"]);
                }
            } else {
                echo json_encode(["error" => "Gagal menyimpan data ke MySQL"]);
            }

            $stmt->close();
        } else {
            echo json_encode(["error" => "Missing required sensor data"]);
        }
    } else {
        echo json_encode(["error" => "Invalid JSON data received"]);
    }
} else {
    echo json_encode(["error" => "Invalid action or missing parameter"]);
}

// Tutup koneksi
$conn->close();
?>
