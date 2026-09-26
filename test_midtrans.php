<?php
/**
 * FILE DIAGNOSTIK KONEKSI MIDTRANS
 * Buka file ini di: http://localhost/holkastore/test_midtrans.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Diagnostik Koneksi Midtrans</h2>";

// 1. Cek File Config
if (!file_exists('midtrans_config.php')) {
    die("<p style='color:red'>❌ Error: File midtrans_config.php tidak ditemukan!</p>");
}
include 'midtrans_config.php';
echo "<p style='color:green'>✅ File Config ditemukan.</p>";

// 2. Cek Library
if (!class_exists('\Midtrans\Config')) {
    die("<p style='color:red'>❌ Error: Library Midtrans GAGAL dimuat. Periksa path di midtrans_config.php.</p>");
}
echo "<p style='color:green'>✅ Library Midtrans berhasil dimuat.</p>";

// 3. Cek CURL extension
if (!function_exists('curl_init')) {
    die("<p style='color:red'>❌ Error: Extension CURL belum aktif di XAMPP Anda. <br>Buka php.ini, cari ';extension=curl' hapis tanda titik komanya, lalu restart Apache.</p>");
}
echo "<p style='color:green'>✅ Extension CURL aktif.</p>";

// 4. Test Create Token (Dummy Data)
echo "<h3>🚀 Mencoba menghubungi server Midtrans...</h3>";

try {
    $transaction_details = [
        'order_id' => 'TEST-' . time(),
        'gross_amount' => 10000,
    ];

    $transaction = [
        'transaction_details' => $transaction_details,
    ];

    $snapToken = \Midtrans\Snap::getSnapToken($transaction);
    
    echo "<div style='background:#dcfce7; color:#166534; padding:20px; border-radius:10px; border:1px solid #bbf7d0;'>";
    echo "<strong>✅ KONEKSI BERHASIL!</strong><br>";
    echo "Snap Token: <code>$snapToken</code><br>";
    echo "<em>Sistem Anda sudah siap digunakan. Silakan coba checkout lagi.</em>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background:#fee2e2; color:#991b1b; padding:20px; border-radius:10px; border:1px solid #fecaca;'>";
    echo "<strong>❌ KONEKSI GAGAL!</strong><br>";
    echo "Pesan Error: " . $e->getMessage();
    echo "<br><br><strong>Tips:</strong> Pastikan internet Anda aktif dan Server Key sudah benar.";
    echo "</div>";
}
?>
