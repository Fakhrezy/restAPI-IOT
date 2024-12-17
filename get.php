<?php
// Periksa apakah ada parameter 'action' dengan nilai 'fetch' pada URL
if (isset($_GET['action']) && $_GET['action'] == 'fetch') {
    // URL Firebase Realtime Database
    $firebaseUrl = 'https://smartgarden-28150-default-rtdb.firebaseio.com/'; // Ganti dengan URL Firebase Anda

    // Menambahkan '.json' di URL untuk mengakses data dalam format JSON
    $firebaseUrlWithJson = $firebaseUrl . '/sensors.json';

    // Menambahkan header Content-Type untuk memastikan response berformat JSON
    header("Content-Type: application/json");

    // Inisialisasi cURL untuk GET request ke Firebase
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $firebaseUrlWithJson);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Timeout 10 detik

    // Eksekusi cURL untuk mengambil data
    $response = curl_exec($ch);

    // Memeriksa apakah cURL berhasil dieksekusi
    if(curl_errno($ch)) {
        echo json_encode(["error" => "cURL Error: " . curl_error($ch)]);
        curl_close($ch);
        exit();
    }

    curl_close($ch);

    // Cek apakah ada data yang diterima
    if ($response) {
        $decodedResponse = json_decode($response, true); // Mengonversi JSON menjadi array asosiatif

        // Mengembalikan data yang diambil dalam format JSON yang lebih rapi
        if (!empty($decodedResponse)) {
            echo json_encode([
                // "message" => "Data berhasil diambil",
                "data" => $decodedResponse
            ], JSON_PRETTY_PRINT); // Menggunakan JSON_PRETTY_PRINT agar tampilannya rapi
        } else {
            echo json_encode([
                "error" => "Tidak ada data ditemukan"
            ], JSON_PRETTY_PRINT);
        }
    } else {
        echo json_encode([
            "error" => "Gagal mengambil data dari Firebase"
        ], JSON_PRETTY_PRINT);
    }
} else {
    echo json_encode([
        "error" => "Action parameter tidak ditemukan atau tidak valid"
    ], JSON_PRETTY_PRINT);
}
?>
