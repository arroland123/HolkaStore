<?php
$host = "localhost";
$user = "fgrrwgzv_admin";
$pass = "LEKb8[PA6W^+~B)C";
$db   = "holkastore";

$conn = mysqli_connect($host, $user, $pass, $db);

if(!$conn){
  die("Koneksi gagal: " . mysqli_connect_error());
}

// Auto-migration: Tambahkan kolom stok_detail jika belum ada
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM `produk` LIKE 'stok_detail'");
if ($check_column && mysqli_num_rows($check_column) == 0) {
    mysqli_query($conn, "ALTER TABLE `produk` ADD COLUMN `stok_detail` TEXT DEFAULT NULL");
}

// Auto-migration: Tambahkan kolom courier jika belum ada
$check_courier = mysqli_query($conn, "SHOW COLUMNS FROM `orders` LIKE 'courier'");
if ($check_courier && mysqli_num_rows($check_courier) == 0) {
    mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN `courier` VARCHAR(50) DEFAULT NULL AFTER notes");
}

// Auto-migration: Tambahkan kolom berat jika belum ada
$check_berat = mysqli_query($conn, "SHOW COLUMNS FROM `produk` LIKE 'berat'");
if ($check_berat && mysqli_num_rows($check_berat) == 0) {
    mysqli_query($conn, "ALTER TABLE `produk` ADD COLUMN `berat` INT NOT NULL DEFAULT 500 AFTER harga");
}

// Auto-migration: Tambahkan kolom destination_city_id jika belum ada
$check_city_id = mysqli_query($conn, "SHOW COLUMNS FROM `orders` LIKE 'destination_city_id'");
if ($check_city_id && mysqli_num_rows($check_city_id) == 0) {
    mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN `destination_city_id` INT DEFAULT NULL AFTER courier");
}

// Auto-migration: Tambahkan kolom no_resi jika belum ada
$check_resi = mysqli_query($conn, "SHOW COLUMNS FROM `orders` LIKE 'no_resi'");
if ($check_resi && mysqli_num_rows($check_resi) == 0) {
    mysqli_query($conn, "ALTER TABLE `orders` ADD COLUMN `no_resi` VARCHAR(100) DEFAULT NULL AFTER destination_city_id");
}
?>
