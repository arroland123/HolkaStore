<?php
session_start();
include 'koneksi.php';

// Jika sudah login, redirect ke admin
if(isset($_SESSION['admin_login'])) {
    header("Location: admin.php");
    exit;
}

$error = '';

// Proses Login
if(isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Query cek admin dari tabel admin
    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username = '$username'");
    
    if(mysqli_num_rows($query) > 0) {
        $admin = mysqli_fetch_assoc($query);
        
        // Cek password menggunakan password_verify karena di register.php password di-hash
        if(password_verify($password, $admin['password'])) {
            $_SESSION['admin_login'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Holka Store</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-user-shield"></i>
            <h1>Login Admin</h1>
            <p>Holka Store</p>
        </div>
        
        <div class="login-body">
            <?php if($error): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
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

                <button type="submit" name="login" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Masuk
                </button>
            </form>
        </div>
    </div>
</body>
</html>