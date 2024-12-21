<?php
// URL Firebase Realtime Database Anda
$firebaseUrl = 'https://smartgarden-28150-default-rtdb.firebaseio.com/sensors.json';  // Ganti dengan URL Firebase Anda

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

// Menampilkan data dengan format JSON yang terstruktur
header('Content-Type: application/json'); // Memberi tahu browser bahwa ini adalah output JSON
echo json_encode(array("sensors" => $data), JSON_PRETTY_PRINT);
?>
