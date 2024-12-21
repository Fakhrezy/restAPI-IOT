<?php
// Aktifkan error reporting untuk debugging
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// URL Firebase Realtime Database (sesuaikan dengan project Anda)
$firebaseUrl = "https://smartgarden-28150-default-rtdb.firebaseio.com/sensors.json";

// Ambil data dari form URL-encoded dengan validasi
$postData = json_decode(file_get_contents('php://input'), true);
$temperature_1 = isset($postData['temperature_1']) ? $postData['temperature_1'] : null;
$turbidity_1 = isset($postData['turbidity_1']) ? $postData['turbidity_1'] : null;
$temperature_2 = isset($postData['temperature_2']) ? $postData['temperature_2'] : null;
$turbidity_2 = isset($postData['turbidity_2']) ? $postData['turbidity_2'] : null;

// Validasi data tersedia
if (is_null($temperature_1) || is_null($turbidity_1) || is_null($temperature_2) || is_null($turbidity_2)) {
    die("Error: Data tidak lengkap. Pastikan semua parameter dikirim.");
}

// Struktur data
$data = [
    "ESP1" => [
        "temperature-1" => (float) $temperature_1,
        "turbidity-1" => (float) $turbidity_1
    ],
    "ESP2" => [
        "temperature-2" => (float) $temperature_2,
        "turbidity-2" => (float) $turbidity_2
    ],
    "timestamp" => date("c") // Format ISO 8601
];

// Inisialisasi CURL ke Firebase
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $firebaseUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

// ambil respons
$response = curl_exec($ch);

if ($response === false) {
    die("Error: " . curl_error($ch));
}

//
echo "Response from Firebase: " . $response;

// Tutup CURL
curl_close($ch);
