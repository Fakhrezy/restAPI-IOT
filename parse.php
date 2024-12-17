<?php
header("Content-Type: application/json");

// Firebase URL
$firebaseUrl = "https://smartgarden-28150-default-rtdb.firebaseio.com/sensors.json"; // Ganti dengan URL Firebase Anda

// Ambil parameter action dari URL
$action = isset($_GET['action']) ? $_GET['action'] : null;

// Fungsi untuk mengambil data dari Firebase
function fetchData($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Nonaktifkan verifikasi SSL jika diperlukan
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Fungsi untuk menghitung statistik (max, min, avg)
function calculateStats($data, $esp, $type) {
    $values = array_column(array_map(function($item) use ($esp, $type) {
        return $item[$esp][$type];
    }, $data), null);

    return [
        "max" => max($values),
        "min" => min($values),
        "avg" => array_sum($values) / count($values)
    ];
}

// Proses berdasarkan action
if ($action === "parse") {
    // Fetch data dari Firebase
    $firebaseData = fetchData($firebaseUrl);

    if ($firebaseData) {
        $data = array_values($firebaseData); // Ubah ke array numerik

        // Hitung statistik untuk ESP1
        $statsESP1 = [
            "temperature" => calculateStats($data, "ESP1", "temperature-1"),
            "turbidity" => calculateStats($data, "ESP1", "turbidity-1")
        ];

        // Hitung statistik untuk ESP2
        $statsESP2 = [
            "temperature" => calculateStats($data, "ESP2", "temperature-2"),
            "turbidity" => calculateStats($data, "ESP2", "turbidity-2")
        ];

        // Output JSON
        echo json_encode([
            "ESP1" => $statsESP1,
            "ESP2" => $statsESP2
        ], JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["error" => "Failed to fetch data from Firebase"]);
    }
} else {
    echo json_encode(["error" => "Invalid action"]);
}
?>
