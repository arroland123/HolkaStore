-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2026 at 08:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `holka_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Kaos'),
(2, 'Jaket'),
(3, 'Celana'),
(4, 'Hoodie'),
(5, 'Set');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `shipping_address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` enum('credit_card','bank_transfer','ewallet','qris','cod') NOT NULL DEFAULT 'credit_card',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `snap_token` text DEFAULT NULL,
  `midtrans_transaction_id` varchar(100) DEFAULT NULL,
  `midtrans_status` varchar(50) DEFAULT NULL,
  `midtrans_payment_type` varchar(50) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address`, `city`, `province`, `postal_code`, `notes`, `payment_method`, `subtotal`, `shipping_cost`, `tax`, `total`, `status`, `payment_status`, `snap_token`, `midtrans_transaction_id`, `midtrans_status`, `midtrans_payment_type`, `tracking_number`, `paid_at`, `shipped_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20260215-B91FB7', 'Abidin', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', 'bank_transfer', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-15 14:45:31', '2026-02-15 14:45:31'),
(2, 'ORD-20260215-4DAAA2', 'Abidin', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', 'bank_transfer', 1350000.00, 0.00, 148500.00, 1498500.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-15 18:26:44', '2026-02-15 18:26:44'),
(3, 'ORD-20260218-D9BE8C', 'Abidin', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', 'credit_card', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-18 15:51:57', '2026-02-18 15:51:57'),
(4, 'ORD-20260220-5B6CB2', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', '', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 15:47:49', '2026-02-20 15:47:49'),
(5, 'ORD-20260220-609518', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', '', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 15:52:22', '2026-02-20 15:52:22'),
(6, 'ORD-20260220-83E092', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', '', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 15:52:24', '2026-02-20 15:52:24'),
(7, 'ORD-20260220-CEDD39', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', '', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 15:52:28', '2026-02-20 15:52:28'),
(8, 'ORD-20260220-5607BE', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', '', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 15:55:17', '2026-02-20 15:55:17'),
(9, 'ORD-20260220-0EAE48', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', '', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 16:01:52', '2026-02-20 16:01:52'),
(10, 'ORD-20260220-EA45E', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', 'credit_card', 10800000.00, 0.00, 1188000.00, 11988000.00, 'pending', 'unpaid', '92e1434d-f457-46bb-ad20-2aa76f3b5537', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-20 16:22:40', '2026-02-20 16:22:41'),
(11, 'ORD-20260220-E74F8', 'hasan', 'ellan2dp@gmail.com', '085697321736', 'Boyolali', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', 'credit_card', 550000.00, 0.00, 60500.00, 610500.00, 'processing', 'paid', 'cd46621b-9ff1-44f3-a847-2e355b0d49b7', 'cace9aaa-b686-4343-ae3a-5700b623c602', 'settlement', 'bank_transfer', NULL, '2026-02-20 23:26:12', NULL, NULL, '2026-02-20 16:25:15', '2026-02-20 16:41:24'),
(12, 'ORD-20260220-D135E', 'ilham', 'ellan2dp@gmail.com', '085697321736', 'colomadu', 'boyolali', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', 'credit_card', 550010.00, 0.00, 60501.00, 610511.00, 'processing', 'paid', '4b8723cc-cef7-4efc-8875-05751b798705', '803a6450-81ff-4c75-a4bb-1fb816a354a8', 'settlement', 'bank_transfer', NULL, '2026-02-20 23:40:19', NULL, NULL, '2026-02-20 16:40:16', '2026-02-20 16:41:19'),
(13, 'ORD-20260220-C0F33', 'IKHSAN GANTENG', 'ellan2dp@gmail.com', '085697321736', 'slamet ryadi', 'surakarta', 'Jawa Tengah', '55287', 'jangan terlambat kak soal nya mau dipakai', 'credit_card', 550010.00, 0.00, 60501.00, 610511.00, 'processing', 'paid', '8ec09271-9610-4b68-a3fe-ed17ed40d054', '4c6a1939-a1e8-49e9-af26-116dbdd52ffb', 'settlement', 'bank_transfer', NULL, '2026-02-20 23:50:27', NULL, NULL, '2026-02-20 16:50:25', '2026-02-20 16:51:15'),
(14, 'ORD-20260220-B9C9F', 'JALU KHECE', 'ellan2dp@gmail.com', '085697321736', 'Alun alun kidul', 'surakarta', 'Jawa Tengah', '55287', 'jangan sampai telat', 'credit_card', 550000.00, 0.00, 60500.00, 610500.00, 'processing', 'paid', '0e631dc4-63de-4ba3-a600-c038b71f92ad', '9311c1a6-3bdc-43c6-bfa2-ed3c8420be5d', 'settlement', 'bank_transfer', NULL, '2026-02-21 01:26:24', NULL, NULL, '2026-02-20 18:26:22', '2026-02-20 18:27:10'),
(15, 'ORD-20260227-661DB', 'ARROLAND ', 'ellan2dp@gmail.com', '085697321736', 'Karanganyar', 'surakarta', 'Jawa Tengah', '55287', 'jangan sampai telat', 'credit_card', 550000.00, 0.00, 60500.00, 610500.00, 'pending', 'unpaid', 'b2a63369-cd03-40d9-91ea-82673a3ccd62', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-27 15:14:41', '2026-02-27 15:14:42'),
(16, 'ORD-20260425-E5E53', 'Muklis', 'ellan2dp@gmail.com', '085697321736', 'Sukoharjo', 'Sukoharjo', 'Jawa Tengah', '55287', 'okee', 'credit_card', 550000.00, 0.00, 60500.00, 610500.00, 'pending', 'unpaid', '01d6075a-5c91-4f1a-bbc8-0237177c58b6', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-25 07:48:51', '2026-04-25 07:48:52'),
(17, 'ORD-20260425-71A78', 'Muklis', 'ellan2dp@gmail.com', '085697321736', 'Sukoharjo', 'Sukoharjo', 'Jawa Tengah', '55287', 'okee', 'credit_card', 550000.00, 0.00, 60500.00, 610500.00, 'pending', 'unpaid', '3e1d3daa-3f92-41a1-8462-aec3179f781c', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-25 07:50:55', '2026-04-25 07:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(10) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `price`, `quantity`, `size`, `color`, `subtotal`, `created_at`) VALUES
(1, 1, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-15 14:45:31'),
(2, 1, 8, 'Stussy Green', 10000000.00, 1, 'S', 'Default', 10000000.00, '2026-02-15 14:45:31'),
(3, 2, 9, 'Stussy White', 800000.00, 1, 'XL', 'Default', 800000.00, '2026-02-15 18:26:44'),
(4, 2, 10, 'Jeans New ', 550000.00, 1, 'L', 'Default', 550000.00, '2026-02-15 18:26:44'),
(5, 3, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-18 15:51:57'),
(6, 3, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-18 15:51:57'),
(7, 4, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-20 15:47:49'),
(8, 4, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-20 15:47:49'),
(9, 5, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-20 15:52:22'),
(10, 5, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-20 15:52:22'),
(11, 6, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-20 15:52:24'),
(12, 6, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-20 15:52:24'),
(13, 7, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-20 15:52:28'),
(14, 7, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-20 15:52:28'),
(15, 8, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-20 15:55:17'),
(16, 8, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-20 15:55:17'),
(17, 9, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-20 16:01:52'),
(18, 9, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-20 16:01:52'),
(19, 10, 9, 'Stussy White', 800000.00, 1, 'L', 'Default', 800000.00, '2026-02-20 16:22:40'),
(20, 10, 8, 'Stussy Green', 10000000.00, 1, 'L', 'Default', 10000000.00, '2026-02-20 16:22:40'),
(21, 11, 10, 'Jeans New ', 550000.00, 1, 'L', 'Default', 550000.00, '2026-02-20 16:25:15'),
(22, 12, 7, 'Kaos', 10.00, 1, 'L', 'Default', 10.00, '2026-02-20 16:40:16'),
(23, 12, 10, 'Jeans New ', 550000.00, 1, 'S', 'Default', 550000.00, '2026-02-20 16:40:16'),
(24, 13, 10, 'Jeans New ', 550000.00, 1, 'S', 'Default', 550000.00, '2026-02-20 16:50:25'),
(25, 13, 7, 'Kaos', 10.00, 1, 'XL', 'Default', 10.00, '2026-02-20 16:50:25'),
(26, 14, 10, 'Jeans New ', 550000.00, 1, 'L', 'Default', 550000.00, '2026-02-20 18:26:22'),
(27, 15, 10, 'Jeans New ', 550000.00, 1, 'M', 'Default', 550000.00, '2026-02-27 15:14:41'),
(28, 16, 10, 'Jeans New ', 550000.00, 1, 'L', 'Default', 550000.00, '2026-04-25 07:48:51'),
(29, 17, 10, 'Jeans New ', 550000.00, 1, 'L', 'Default', 550000.00, '2026-04-25 07:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(11) NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `nama_produk`, `id_kategori`, `deskripsi`, `harga`, `stok`, `gambar`) VALUES
(6, 'baju percobaan', 1, NULL, 12, 12, 'Screenshot 2025-12-15 233050.png'),
(7, 'Kaos', 1, 'KAOS TERBAIK DI TOKO', 10, 12, 'images percobaan.jpeg'),
(8, 'Stussy Green', 1, 'salah satu varian yang populer dari brand streetwear ikonik asal California ini, terutama karena memberikan nuansa earthy, santai, dan vintage.', 10000000, 0, 'Stussy x Nike Crossover Alphabet Printing Round Neck Short Sleeve Unisex Green DD3343-341 US L.jpeg'),
(9, 'Stussy White', 1, 'salah satu varian yang populer dari brand streetwear ikonik asal California ini, terutama karena memberikan nuansa earthy, santai, dan vintage.', 800000, 0, 'BEAMS（ビームス）公式サイト.jpeg'),
(10, 'Jeans New ', 3, 'Jeans new brand', 550000, 42, 'Men\'s Jeans - Shop Men\'s Jeans Online.jpeg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
