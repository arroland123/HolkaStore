<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?? "Holka Store"; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/script.js" defer></script>
</head>
<body>

<header class="header">
  <div class="container header-flex">

    <!-- Hamburger KIRI -->
    <div class="hamburger" onclick="toggleMenu()">☰</div>

    <!-- Logo TENGAH -->
    <div class="logo">HOLKA STORE</div>

    <!-- Menu KANAN -->
    <nav class="nav-menu">
      <a href="index.php">Beranda</a>
      <a href="produk.php">Produk</a>
      <a href="login.php">Login</a>
    </nav>

  </div>
</header>

<!-- Sidebar Kategori -->
<div id="sidebar" class="sidebar">
  <h3>Kategori</h3>
  <a href="?kategori=Baju">Baju</a>
  <a href="?kategori=Jaket">Jaket</a>
  <a href="?kategori=Celana">Celana</a>
</div>

<div id="overlay" class="overlay" onclick="toggleMenu()"></div>
