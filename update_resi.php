<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

session_start();
include 'koneksi.php';

// Load PHPMailer files
require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

// Cek autentikasi admin
if (!isset($_SESSION['admin_login'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $no_resi = isset($_POST['no_resi']) ? trim($_POST['no_resi']) : '';
    
    if ($order_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID Pesanan tidak valid.']);
        exit;
    }
    
    $safe_resi = mysqli_real_escape_string($conn, $no_resi);
    
    // Update no_resi di tabel orders
    if ($no_resi === '') {
        $query = "UPDATE orders SET no_resi = NULL WHERE id = $order_id";
    } else {
        $query = "UPDATE orders SET no_resi = '$safe_resi' WHERE id = $order_id";
    }
    
    if (mysqli_query($conn, $query)) {
        $email_sent = false;
        $email_error = '';
        
        // Jika berhasil diupdate dan resi tidak kosong, kirim email
        if ($no_resi !== '') {
            $email_query = "SELECT customer_name, customer_email, order_number, courier FROM orders WHERE id = $order_id";
            $email_result = mysqli_query($conn, $email_query);
            
            if ($email_result && mysqli_num_rows($email_result) > 0) {
                $order_data = mysqli_fetch_assoc($email_result);
                $to = $order_data['customer_email'];
                $buyer_name = $order_data['customer_name'];
                $order_number = $order_data['order_number'];
                $courier = !empty($order_data['courier']) ? $order_data['courier'] : 'JNE Express';
                
                if (!empty($to)) {
                    $mail = new PHPMailer(true);
                    
                    try {
                        // Konfigurasi Server SMTP
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'holkastore11@gmail.com';
                        $mail->Password   = 'rdrb ujzs tzxw dnbj';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port       = 587;
                        
                        // Pengirim & Penerima
                        $mail->setFrom('holkastore11@gmail.com', 'Holka Store');
                        $mail->addAddress($to, $buyer_name);
                        $mail->addReplyTo('holkastore11@gmail.com', 'Holka Store Support');
                        
                        // Format Email HTML
                        $mail->isHTML(true);
                        $mail->Subject = 'Detail Pengiriman Pesanan Anda #' . $order_number . ' - Holka Store';
                        
                        // Template HTML Email Premium & Menarik
                        $email_template = '
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <meta charset="utf-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Pesanan Anda Telah Dikirim</title>
                            <style>
                                body {
                                    font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                                    background-color: #f4f6f9;
                                    color: #333333;
                                    margin: 0;
                                    padding: 0;
                                    -webkit-font-smoothing: antialiased;
                                }
                                .email-wrapper {
                                    width: 100%;
                                    background-color: #f4f6f9;
                                    padding: 20px 0;
                                }
                                .email-container {
                                    max-width: 600px;
                                    margin: 0 auto;
                                    background-color: #ffffff;
                                    border-radius: 12px;
                                    overflow: hidden;
                                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                                }
                                .email-header {
                                    background: linear-gradient(135deg, #4169E1 0%, #1e3a8a 100%);
                                    padding: 35px 20px;
                                    text-align: center;
                                    color: #ffffff;
                                }
                                .email-header h1 {
                                    margin: 0;
                                    font-size: 26px;
                                    font-weight: 700;
                                    letter-spacing: 1px;
                                    text-transform: uppercase;
                                }
                                .email-header p {
                                    margin: 5px 0 0 0;
                                    font-size: 14px;
                                    opacity: 0.9;
                                }
                                .email-body {
                                    padding: 30px 25px;
                                }
                                .welcome-text {
                                    font-size: 18px;
                                    font-weight: 600;
                                    color: #1e3a8a;
                                    margin-top: 0;
                                    margin-bottom: 15px;
                                }
                                .intro-text {
                                    font-size: 15px;
                                    line-height: 1.6;
                                    color: #555555;
                                    margin-bottom: 25px;
                                }
                                .resi-card {
                                    background-color: #f0f4ff;
                                    border-left: 4px solid #4169E1;
                                    padding: 20px;
                                    border-radius: 0 8px 8px 0;
                                    margin-bottom: 25px;
                                }
                                .badge-resi {
                                    background-color: #4169E1;
                                    color: #ffffff;
                                    padding: 3px 8px;
                                    border-radius: 4px;
                                    font-family: \'Courier New\', Courier, monospace;
                                    font-weight: bold;
                                    letter-spacing: 0.5px;
                                }
                                .btn-track {
                                    background: linear-gradient(135deg, #4169E1 0%, #3359c2 100%);
                                    color: #ffffff !important;
                                    text-decoration: none;
                                    padding: 14px 30px;
                                    font-weight: 600;
                                    font-size: 15px;
                                    border-radius: 30px;
                                    display: inline-block;
                                    box-shadow: 0 4px 10px rgba(65, 105, 225, 0.3);
                                }
                                .footer {
                                    background-color: #f9fafb;
                                    padding: 25px 20px;
                                    text-align: center;
                                    border-top: 1px solid #edf2f7;
                                }
                                .footer p {
                                    margin: 5px 0;
                                    font-size: 13px;
                                    color: #718096;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="email-wrapper">
                                <div class="email-container">
                                    <div class="email-header">
                                        <h1>Holka Store</h1>
                                        <p>Premium Minimalist Fashion</p>
                                    </div>
                                    <div class="email-body">
                                        <h2 class="welcome-text">Halo ' . htmlspecialchars($buyer_name) . ',</h2>
                                        <p class="intro-text">Kabar baik! Paket pesanan Anda #' . htmlspecialchars($order_number) . ' saat ini telah kami serahkan ke kurir pengiriman. Berikut adalah informasi detail resi pengiriman untuk melacak paket Anda:</p>
                                        
                                        <div class="resi-card">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="5">
                                                <tr>
                                                    <td width="30%" style="font-weight: 600; color: #555555; font-size: 14px;">No. Pesanan</td>
                                                    <td width="70%" style="color: #222222; font-size: 14px;"><strong>#' . htmlspecialchars($order_number) . '</strong></td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600; color: #555555; font-size: 14px;">Jasa Kurir</td>
                                                    <td style="color: #222222; font-size: 14px;">' . htmlspecialchars($courier) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600; color: #555555; font-size: 14px;">Nomor Resi</td>
                                                    <td style="color: #222222; font-size: 14px;"><span class="badge-resi">' . htmlspecialchars($no_resi) . '</span></td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight: 600; color: #555555; font-size: 14px;">Tanggal Kirim</td>
                                                    <td style="color: #222222; font-size: 14px;">' . date('d F Y') . '</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <p class="intro-text" style="margin-top: 20px;">Mohon tunggu status pelacakan kurir terupdate dalam waktu 1x24 jam sejak email ini diterima. Terima kasih telah berbelanja di Holka Store!</p>
                                    </div>
                                    <div class="footer">
                                        <p>&copy; ' . date('Y') . ' Holka Store. All Rights Reserved.</p>
                                        <p>Jika butuh bantuan, hubungi kami di <a href="mailto:holkastore11@gmail.com" style="color: #4169E1; text-decoration: none;">holkastore11@gmail.com</a></p>
                                    </div>
                                </div>
                            </div>
                        </body>
                        </html>
                        ';
                        
                        $mail->Body = $email_template;
                        $mail->AltBody = "Halo {$buyer_name},\n\nPesanan Anda #{$order_number} telah dikirim menggunakan kurir {$courier}.\nNomor Resi Anda: {$no_resi}\n\nTerima kasih telah berbelanja di Holka Store!";
                        
                        $mail->send();
                        $email_sent = true;
                    } catch (Exception $e) {
                        $email_error = $mail->ErrorInfo;
                    }
                }
            }
        }
        
        echo json_encode([
            'success' => true, 
            'email_sent' => $email_sent, 
            'email_error' => $email_error
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}
?>
