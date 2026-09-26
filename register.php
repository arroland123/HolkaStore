<?php
session_start();
include 'koneksi.php';

// Jika sudah login, redirect ke admin
if(isset($_SESSION['admin_login'])) {
    header("Location: admin.php");
    exit;
}

$error = '';
$success = '';

// Proses Register
if(isset($_POST['register'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Cek apakah username sudah ada
    $check_query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username'");
    if(mysqli_num_rows($check_query) > 0) {
        $error = "Username sudah digunakan!";
    } else if($password != $confirm_password) {
        $error = "Password tidak cocok!";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert ke database
        $insert_query = mysqli_query($conn, "INSERT INTO admin (username, password) VALUES ('$username', '$hashed_password')");
        
        if($insert_query) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Gagal melakukan registrasi!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Admin - Holka Store</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-alert {
            background: #e6ffe6;
            color: #006600;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #006600;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-plus"></i>
            <h1>Register Admin</h1>
            <p>Holka Store</p>
        </div>
        
        <div class="login-body">
            <?php if($error): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <?php if($success): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i> <?= $success ?>
            </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Konfirmasi Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="confirm_password" placeholder="Konfirmasi password" required>
                    </div>
                </div>

                <button type="submit" name="register" class="btn-login">
                    <i class="fas fa-user-plus"></i> Daftar
                </button>
            </form>
            
            <div class="back-link" style="margin-top: 15px; text-align: center;">
                <a href="login.php">Sudah punya akun? Login di sini</a>
            </div>
        </div>
    </div>
</body>
</html>
