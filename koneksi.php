<?php
$servername = "localhost";
$username = "root";  // ganti dengan username database Anda
$password = "";  // ganti dengan password database Anda
$dbname = "db_iot_tb";  // ganti dengan nama database Anda

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
