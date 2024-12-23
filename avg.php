<?php

// URL Firebase Realtime Database Anda
$firebaseUrl = 'https://smartgarden-28150-default-rtdb.firebaseio.com/sensors.json'; // Ganti <project-id> dengan project Anda

// Fungsi untuk mengambil data dari Firebase
function fetchDataFromFirebase($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Jika SSL tidak valid
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// Ambil data dari Firebase
$data = fetchDataFromFirebase($firebaseUrl);

// Periksa apakah data berhasil diambil
if (!$data) {
    die(json_encode(['error' => 'Gagal mengambil data dari Firebase']));
}

// Array untuk menyimpan data per jam
$groupedData = [];

// Mengelompokkan data berdasarkan jam
foreach ($data as $key => $entry) {
    if (isset($entry['timestamp'])) {
        $timestamp = $entry['timestamp'];
        $hour = date('Y-m-d H:00:00', strtotime($timestamp)); // Format per jam

        // Inisialisasi grup jika belum ada
        if (!isset($groupedData[$hour])) {
            $groupedData[$hour] = ['count' => 0, 'sums' => []];
        }

        // Menambahkan nilai-nilai dari ESP1 dan ESP2
        foreach ($entry as $espKey => $values) {
            if (is_array($values)) { // Pastikan ini adalah array ESP data
                foreach ($values as $parameter => $value) {
                    if (!isset($groupedData[$hour]['sums'][$parameter])) {
                        $groupedData[$hour]['sums'][$parameter] = 0;
                    }
                    $groupedData[$hour]['sums'][$parameter] += $value;
                }
            }
        }

        // Tambahkan penghitung untuk grup
        $groupedData[$hour]['count'] += 1;
    }
}

// Menghitung rata-rata untuk setiap jam
$averages = [];
foreach ($groupedData as $hour => $data) {
    $averages[$hour] = [];
    foreach ($data['sums'] as $parameter => $sum) {
        $averages[$hour][$parameter] = $sum / $data['count'];
    }
}

// Mengubah hasil rata-rata ke format JSON
header('Content-Type: application/json');
echo json_encode($averages, JSON_PRETTY_PRINT);

