<?php
/**
 * Holka Store - Standalone Email Receipt Sender (PHPMailer Test)
 * 
 * Script ini digunakan untuk menguji pengiriman email berisi nomor resi
 * ke pembeli menggunakan PHPMailer dengan SMTP Gmail.
 */

// Load PHPMailer files
require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$message_status = '';
$debug_output = '';
$status_type = ''; // success or error

// Nilai default untuk formulir
$default_email = 'arrolandrossy@gmail.com';
$default_name = 'Arro Land Rossy';
$default_order_id = 'HK-2026-0723';
$default_courier = 'JNE Express';
$default_resi = 'RESI' . rand(100000000, 999999999) . 'ID';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari formulir
    $buyer_email = filter_input(INPUT_POST, 'buyer_email', FILTER_VALIDATE_EMAIL);
    $buyer_name = htmlspecialchars(trim($_POST['buyer_name'] ?? $default_name));
    $order_id = htmlspecialchars(trim($_POST['order_id'] ?? $default_order_id));
    $courier = htmlspecialchars(trim($_POST['courier'] ?? $default_courier));
    $resi_number = htmlspecialchars(trim($_POST['resi_number'] ?? $default_resi));

    if (!$buyer_email) {
        $message_status = "Alamat email pembeli tidak valid.";
        $status_type = "error";
    } else {
        $mail = new PHPMailer(true);

        try {
            // Setup Output Buffering untuk menangkap debug log SMTP jika terjadi error
            ob_start();
            
            // Konfigurasi Server SMTP
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;              // Aktifkan debug output
            $mail->isSMTP();                                    // Menggunakan SMTP
            $mail->Host       = 'smtp.gmail.com';               // Server SMTP Gmail
            $mail->SMTPAuth   = true;                           // Aktifkan autentikasi SMTP
            $mail->Username   = 'holkastore11@gmail.com';       // Email Holka Store
            $mail->Password   = 'rdrb ujzs tzxw dnbj';           // App Password Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enkripsi TLS
            $mail->Port       = 587;                            // Port SMTP TLS

            // Pengirim & Penerima
            $mail->setFrom('holkastore11@gmail.com', 'Holka Store');
            $mail->addAddress($buyer_email, $buyer_name);
            $mail->addReplyTo('holkastore11@gmail.com', 'Holka Store Support');

            // Format Email HTML
            $mail->isHTML(true);
            $mail->Subject = 'Detail Pengiriman Pesanan Anda #' . $order_id . ' - Holka Store';
            
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
                    .resi-row {
                        display: flex;
                        margin-bottom: 10px;
                    }
                    .resi-row:last-child {
                        margin-bottom: 0;
                    }
                    .resi-label {
                        width: 130px;
                        font-weight: 600;
                        color: #555555;
                        font-size: 14px;
                    }
                    .resi-value {
                        flex: 1;
                        color: #222222;
                        font-size: 14px;
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
                    .btn-track-container {
                        text-align: center;
                        margin: 30px 0;
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
                        transition: all 0.3s ease;
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
                    .footer-links {
                        margin-top: 15px;
                    }
                    .footer-links a {
                        color: #4169E1;
                        text-decoration: none;
                        margin: 0 10px;
                        font-size: 13px;
                    }
                </style>
            </head>
            <body>
                <div class="email-wrapper">
                    <div class="email-container">
                        <!-- Header -->
                        <div class="email-header">
                            <h1>Holka Store</h1>
                            <p>Premium Minimalist Fashion</p>
                        </div>
                        
                        <!-- Body -->
                        <div class="email-body">
                            <h2 class="welcome-text">Halo ' . $buyer_name . ',</h2>
                            <p class="intro-text">Kabar baik! Paket pesanan Anda #' . $order_id . ' saat ini telah kami serahkan ke kurir pengiriman. Berikut adalah informasi detail resi pengiriman untuk melacak paket Anda:</p>
                            
                            <!-- Resi Card -->
                            <div class="resi-card">
                                <table width="100%" border="0" cellspacing="0" cellpadding="5">
                                    <tr>
                                        <td width="30%" style="font-weight: 600; color: #555555; font-size: 14px;">No. Pesanan</td>
                                        <td width="70%" style="color: #222222; font-size: 14px;"><strong>#' . $order_id . '</strong></td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 600; color: #555555; font-size: 14px;">Jasa Kurir</td>
                                        <td style="color: #222222; font-size: 14px;">' . $courier . '</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 600; color: #555555; font-size: 14px;">Nomor Resi</td>
                                        <td style="color: #222222; font-size: 14px;"><span class="badge-resi">' . $resi_number . '</span></td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight: 600; color: #555555; font-size: 14px;">Tanggal Kirim</td>
                                        <td style="color: #222222; font-size: 14px;">' . date('d F Y') . '</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <!-- Action Button -->
                            <div class="btn-track-container">
                                <a href="https://www.cekresi.com/?noresi=' . urlencode($resi_number) . '" target="_blank" style="color: #ffffff;" class="btn-track">Lacak Perjalanan Paket</a>
                            </div>
                            
                            <p class="intro-text" style="margin-top: 20px;">Mohon tunggu status pelacakan kurir terupdate dalam waktu 1x24 jam sejak email ini diterima. Terima kasih telah berbelanja di Holka Store!</p>
                        </div>
                        
                        <!-- Footer -->
                        <div class="footer">
                            <p>&copy; ' . date('Y') . ' Holka Store. All Rights Reserved.</p>
                            <p>Jika butuh bantuan, hubungi kami di <a href="mailto:holkastore11@gmail.com" style="color: #4169E1; text-decoration: none;">holkastore11@gmail.com</a></p>
                            <div class="footer-links">
                                <a href="#">Tentang Kami</a> | <a href="#">Kebijakan Privasi</a> | <a href="#">Hubungi Kami</a>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            ';

            $mail->Body = $email_template;
            
            // Set Alt Body untuk email client non-HTML
            $mail->AltBody = "Halo {$buyer_name},\n\nPesanan Anda #{$order_id} telah dikirim menggunakan kurir {$courier}.\nNomor Resi Anda: {$resi_number}\n\nTerima kasih telah berbelanja di Holka Store!";

            $mail->send();
            
            // Bersihkan output buffer debug dan set status sukses
            ob_end_clean();
            $message_status = "Email berhasil dikirim ke <strong>" . htmlspecialchars($buyer_email) . "</strong>!";
            $status_type = "success";
            
        } catch (Exception $e) {
            // Ambil debug log jika terjadi kesalahan
            $debug_output = ob_get_clean();
            $message_status = "Gagal mengirim email: " . $mail->ErrorInfo;
            $status_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uji Kirim Email Resi - Holka Store</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4169E1;
            --primary-dark: #1e3a8a;
            --primary-light: #eef2ff;
            --success: #10b981;
            --success-light: #ecfdf5;
            --error: #ef4444;
            --error-light: #fef2f2;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --bg-body: #f8fafc;
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 650px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
            transition: var(--transition);
        }

        .brand-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
            position: relative;
        }

        .brand-header h1 {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .brand-header p {
            font-size: 14px;
            opacity: 0.85;
            font-weight: 300;
        }

        .brand-header .icon-badge {
            background: rgba(255, 255, 255, 0.15);
            width: 50px;
            height: 50px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 20px;
            backdrop-filter: blur(5px);
        }

        .form-body {
            padding: 35px 40px;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 16px;
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            padding: 13px 15px 13px 45px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            color: var(--text-main);
            outline: none;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(65, 105, 225, 0.15);
        }

        .form-control:focus + i {
            color: var(--primary);
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(65, 105, 225, 0.25);
            transition: var(--transition);
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(65, 105, 225, 0.35);
        }

        .btn-submit:active {
            transform: translateY(1px);
        }

        /* Status Alert Banner */
        .alert-banner {
            padding: 18px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            animation: fadeIn 0.4s ease-out;
        }

        .alert-banner.success {
            background-color: var(--success-light);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #065f46;
        }

        .alert-banner.error {
            background-color: var(--error-light);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #991b1b;
        }

        .alert-icon {
            font-size: 20px;
            margin-top: 2px;
        }

        .alert-content h4 {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .alert-content p {
            font-size: 13.5px;
            line-height: 1.4;
            opacity: 0.9;
        }

        /* Debug Logs Area */
        .debug-container {
            margin-top: 25px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .debug-header {
            background-color: #f1f5f9;
            padding: 10px 15px;
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .debug-body {
            background-color: #0f172a;
            color: #38bdf8;
            padding: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
            line-height: 1.5;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 576px) {
            .form-body {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="brand-header">
            <div class="icon-badge">
                <i class="fas fa-paper-plane"></i>
            </div>
            <h1>Holka Store Mailer</h1>
            <p>Sistem Pengujian Email & Pengiriman Nomor Resi Pembeli</p>
        </div>

        <div class="form-body">
            <!-- Alert Info Status -->
            <?php if (!empty($message_status)): ?>
                <div class="alert-banner <?php echo $status_type; ?>">
                    <div class="alert-icon">
                        <?php if ($status_type === 'success'): ?>
                            <i class="fas fa-check-circle"></i>
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="alert-content">
                        <h4><?php echo $status_type === 'success' ? 'Pengiriman Berhasil' : 'Pengiriman Gagal'; ?></h4>
                        <p><?php echo $message_status; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Uji Coba -->
            <form action="" method="POST" id="testEmailForm">
                <div class="form-group">
                    <label for="buyer_name">Nama Pembeli</label>
                    <div class="input-wrapper">
                        <input type="text" name="buyer_name" id="buyer_name" class="form-control" value="<?php echo htmlspecialchars($buyer_name ?? $default_name); ?>" required>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="buyer_email">Email Pembeli (Tujuan)</label>
                    <div class="input-wrapper">
                        <input type="email" name="buyer_email" id="buyer_email" class="form-control" value="<?php echo htmlspecialchars($buyer_email ?? $default_email); ?>" required>
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="order_id">ID / Nomor Pesanan</label>
                    <div class="input-wrapper">
                        <input type="text" name="order_id" id="order_id" class="form-control" value="<?php echo htmlspecialchars($order_id ?? $default_order_id); ?>" required>
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="courier">Jasa Pengiriman (Kurir)</label>
                    <div class="input-wrapper">
                        <input type="text" name="courier" id="courier" class="form-control" value="<?php echo htmlspecialchars($courier ?? $default_courier); ?>" required>
                        <i class="fas fa-truck"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="resi_number">Nomor Resi</label>
                    <div class="input-wrapper">
                        <input type="text" name="resi_number" id="resi_number" class="form-control" value="<?php echo htmlspecialchars($resi_number ?? $default_resi); ?>" required>
                        <i class="fas fa-barcode"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <i class="fas fa-paper-plane"></i> Kirim Email ke Tujuan
                </button>
            </form>

            <!-- Debug Logs SMTP jika gagal -->
            <?php if (!empty($debug_output)): ?>
                <div class="debug-container">
                    <div class="debug-header">
                        <span>SMTP Debug Log (PHPMailer)</span>
                        <i class="fas fa-terminal"></i>
                    </div>
                    <div class="debug-body"><?php echo htmlspecialchars($debug_output); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.getElementById('testEmailForm').addEventListener('submit', function() {
            var btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sedang Mengirim...';
        });
    </script>
</body>
</html>
