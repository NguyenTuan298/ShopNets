-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 03:30 PM
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
-- Database: `shopnets`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `email`, `phone`, `avatar`, `password`, `role`, `created_at`) VALUES
(1, 'admin@shopnet.com', '0984831424', 'assets/images/uploads/avatar_1763086702_6916916eb2f2e.jpg', '$2y$10$M5VccEUAL6xamNY.YL5kzOYwLl.D6TfQnHfJNPqst33hkONPkMzPe', 'admin', '2025-11-01 08:27:30');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Điện thoại', 'Thiết bị di động và điện thoại thông minh', 'cat_692296c544655.png', '2025-11-07 15:11:41', '2025-11-23 05:08:21'),
(2, 'Laptop', 'Máy tính xách tay', 'cat_69228af5e0dad.jpg', '2025-11-07 15:11:41', '2025-11-23 04:17:57'),
(3, 'Tablet', 'Tablet computers and accessories', 'cat_69228bc11013a.jpg', '2025-11-07 15:11:41', '2025-11-23 04:21:21'),
(4, 'Điện tử – Công nghệ', '', 'cat_6922993b93639.jpg', '2025-11-21 12:38:40', '2025-11-23 05:18:51'),
(5, 'Đồ gia dụng – Điện máy', '', 'cat_6922989b90612.jpg', '2025-11-21 06:24:41', '2025-11-23 05:16:11'),
(6, 'Thời trang', '', 'cat_69229a045890f.jpg', '2025-11-21 06:24:55', '2025-11-23 05:22:12'),
(7, 'Làm đẹp – Sức khỏe', '', 'cat_69229a191deee.jpg', '2025-11-21 06:25:11', '2025-11-23 05:22:33'),
(8, 'Mẹ & Bé', '', 'cat_69229a29c877d.jpg', '2025-11-21 06:25:17', '2025-11-23 05:22:49'),
(9, 'Nội thất – Trang trí nhà cửa', '', 'cat_692299fabf311.jpg', '2025-11-21 06:25:26', '2025-11-23 05:22:02'),
(10, 'Thể thao', '', 'cat_69229a36ec934.jpg', '2025-11-21 06:25:38', '2025-11-23 05:23:02'),
(11, 'Thực phẩm – Bách hóa', '', 'cat_69229a68a80df.jpg', '2025-11-21 06:26:09', '2025-11-23 05:23:52'),
(12, 'Sách', '', 'cat_69229a86de4a4.jpg', '2025-11-21 06:26:25', '2025-11-23 05:24:22');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `billing_address` text DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('cod','bank_transfer','momo','vnpay') DEFAULT 'cod',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_method` varchar(100) DEFAULT NULL,
  `tracking_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_email`, `customer_name`, `customer_phone`, `shipping_address`, `billing_address`, `subtotal`, `shipping_fee`, `tax_amount`, `discount_amount`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_method`, `tracking_number`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-001', NULL, 'john@example.com', 'John Smith', '0901234567', '123 Main St, District 1, Ho Chi Minh City', '123 Main St, District 1, Ho Chi Minh City', 31000000.00, 50000.00, 0.00, 0.00, 31050000.00, 'cod', 'pending', 'shipped', 'Standard Shipping', NULL, 'Customer requested fast delivery', '2024-11-10 03:30:00', '2024-11-11 07:20:00'),
(2, 'ORD-002', 2, 'sarah@example.com', 'Sarah Johnson', '0902345678', '456 Oak Ave, District 3, Ho Chi Minh City', '456 Oak Ave, District 3, Ho Chi Minh City', 6000000.00, 30000.00, 0.00, 0.00, 6030000.00, 'bank_transfer', 'paid', 'cancelled', 'Express Shipping', NULL, NULL, '2024-11-11 08:45:00', '2025-11-24 16:11:42'),
(3, 'ORD-003', 3, 'mike@example.com', 'Mike Brown', '0903456789', '789 Pine St, District 7, Ho Chi Minh City', '789 Pine St, District 7, Ho Chi Minh City', 28000000.00, 50000.00, 0.00, 0.00, 28050000.00, 'vnpay', 'refunded', 'cancelled', 'Standard Shipping', NULL, 'Product was damaged during shipping', '2024-11-09 04:20:00', '2024-11-12 02:30:00'),
(4, 'ORD-004', 4, 'emily@example.com', 'Emily Davis', '0904567890', '321 Elm St, District 2, Ho Chi Minh City', '321 Elm St, District 2, Ho Chi Minh City', 12000000.00, 40000.00, 0.00, 0.00, 12040000.00, 'momo', 'paid', 'shipped', 'Express Shipping', NULL, NULL, '2024-11-12 02:15:00', '2024-11-13 09:40:00'),
(5, 'ORD-005', 5, 'david@example.com', 'David Wilson', '0905678901', '654 Maple Ave, District 5, Ho Chi Minh City', '654 Maple Ave, District 5, Ho Chi Minh City', 31000000.00, 50000.00, 0.00, 0.00, 31050000.00, 'cod', 'pending', 'shipped', 'Standard Shipping', NULL, 'Customer will pay on delivery', '2024-11-13 09:30:00', '2025-11-24 16:09:56'),
(6, 'ORD202511251533016830', 1, 'john@example.com', 'Nguyen Tuan', '0984831424', '2 tô ký trung chánh', NULL, 456789.00, 30000.00, 0.00, 0.00, 486789.00, 'momo', 'pending', 'cancelled', NULL, NULL, 'vvvv', '2025-11-25 14:33:01', '2025-11-25 14:33:52'),
(7, 'ORD202511251534307778', 1, 'john@example.com', 'Nguyen Tuan', '0984831424', '2 tô ký trung chánh', NULL, 1232774.00, 30000.00, 0.00, 0.00, 1262774.00, 'momo', 'pending', 'cancelled', NULL, NULL, '', '2025-11-25 14:34:30', '2025-11-25 14:43:37'),
(8, 'ORD202511251541133653', 1, 'john@example.com', 'Nguyen Tuan', '0984831424', '2 tô ký trung chánh', NULL, 15248174.00, 0.00, 0.00, 0.00, 15248174.00, 'cod', 'pending', 'cancelled', NULL, NULL, '', '2025-11-25 14:41:13', '2025-11-25 14:43:39'),
(9, 'ORD202511251615488042', 1, 'john@example.com', 'Nguyen Tuan', '0984831424', '2 tô ký trung chánh', NULL, 456789.00, 30000.00, 0.00, 0.00, 486789.00, 'cod', 'pending', 'confirmed', NULL, NULL, '', '2025-11-25 15:15:48', '2025-11-26 05:43:21'),
(24, 'ORD202511260954327608', 8, 'test@gmail.com', 'test', '123', '123', NULL, 6824895.00, 0.00, 0.00, 0.00, 6824895.00, 'momo', 'paid', 'confirmed', NULL, NULL, '', '2025-11-26 08:54:32', '2025-11-26 08:54:32'),
(25, 'ORD202511261041272429', 8, 'test@gmail.com', 'test', '123', '123', NULL, 345678.00, 30000.00, 0.00, 0.00, 375678.00, 'cod', 'pending', 'confirmed', NULL, NULL, '', '2025-11-26 09:41:27', '2025-11-26 10:27:43');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `image`, `product_name`, `product_price`, `quantity`, `total_price`, `attributes`, `created_at`) VALUES
(8, 6, 94, NULL, 'Trà túi lọc', 456789.00, 1, 456789.00, NULL, '2025-11-25 14:33:01'),
(9, 7, 31, NULL, 'iPad 9', 1232774.00, 1, 1232774.00, NULL, '2025-11-25 14:34:30'),
(10, 8, 38, NULL, 'Camera WiFi', 15248174.00, 1, 15248174.00, NULL, '2025-11-25 14:41:13'),
(11, 9, 94, NULL, 'Trà túi lọc', 456789.00, 1, 456789.00, NULL, '2025-11-25 15:15:48'),
(36, 24, 13, NULL, 'Xiaomi 13', 6479217.00, 1, 6479217.00, NULL, '2025-11-26 08:54:32'),
(37, 24, 93, NULL, 'Dầu ăn', 345678.00, 1, 345678.00, NULL, '2025-11-26 08:54:32'),
(38, 25, 93, NULL, 'Dầu ăn', 345678.00, 1, 345678.00, NULL, '2025-11-26 09:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `note`, `created_at`) VALUES
(1, 1, 'pending', 'Order placed successfully', '2024-11-10 03:30:00'),
(2, 1, 'confirmed', 'Order confirmed by admin', '2024-11-10 07:00:00'),
(3, 1, 'processing', 'Items are being prepared', '2024-11-11 02:00:00'),
(4, 1, 'shipped', 'Order shipped via Standard Shipping', '2024-11-11 07:20:00'),
(5, 2, 'pending', 'Order placed successfully', '2024-11-11 08:45:00'),
(6, 3, 'pending', 'Order placed successfully', '2024-11-09 04:20:00'),
(7, 3, 'confirmed', 'Order confirmed by admin', '2024-11-09 08:30:00'),
(8, 3, 'cancelled', 'Product damaged during shipping, order cancelled and refunded', '2024-11-12 02:30:00'),
(9, 4, 'pending', 'Order placed successfully', '2024-11-12 02:15:00'),
(10, 4, 'confirmed', 'Order confirmed by admin', '2024-11-12 06:00:00'),
(11, 4, 'processing', 'Items are being prepared', '2024-11-13 03:00:00'),
(12, 4, 'shipped', 'Order shipped via Express Shipping', '2024-11-13 09:40:00'),
(13, 5, 'pending', 'Order placed successfully', '2024-11-13 09:30:00'),
(14, 5, 'confirmed', '', '2025-11-24 16:09:09'),
(15, 5, 'processing', 'Đã giao', '2025-11-24 16:09:49'),
(16, 5, 'shipped', '', '2025-11-24 16:09:56'),
(17, 2, 'confirmed', '', '2025-11-24 16:11:06'),
(18, 2, 'cancelled', '', '2025-11-24 16:11:42'),
(19, 9, 'confirmed', '', '2025-11-26 05:43:21'),
(21, 25, 'confirmed', '', '2025-11-26 10:27:43');

-- --------------------------------------------------------

--
-- Table structure for table `payment_logs`
--

CREATE TABLE `payment_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `response_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `compare_price` decimal(12,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `short_description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `price`, `compare_price`, `quantity`, `description`, `specifications`, `short_description`, `image`, `slug`, `featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 15 Pro', 1, 10000000.00, 0.00, 100, '', NULL, '', 'product_1763382757_691b15e534b83.jpg', 'iphone-15-pro', 1, 1, '2025-11-17 05:11:41', '2025-11-18 07:45:47'),
(2, 'Laptop Dell Inspiron 15 3520', 2, 10000000.00, 0.00, 10, '', '{\"Màn hình\":\"15.6\",\"CPU\":\"Intel Core i5 Alder Lake - 1235U\",\"RAM\":\"16 GB\",\"Ổ cứng\":\"512 GB SSD NVMe PCIe (Có thể tháo ra, lắp thanh khác tối đa 2 TB)\",\"Pin\":\"3-cell Li-ion, 41 Wh\",\"Card đồ hoa\":\"15.6\"}', '', 'product_1763481256_691c96a81a7f0.jpg', 'laptop-dell-inspiron-15-3520', 0, 1, '2025-11-18 08:54:16', '2025-11-18 09:17:32'),
(12, 'Vivo V27', 1, 4857624.00, 0.00, 195, '', '{\"Màn hình\":\"AMOLED, 6.78 inch  Độ phân giải: 2400 × 1080 pixels (FHD+)  Tần số quét: 120Hz\",\"CPU\":\"MediaTek Dimensity 7200 (4nm)\",\"RAM\":\"8GB / 12GB\",\"Ổ cứng\":\"128GB / 256GB\",\"Pin\":\"Pin: 4600 mAh  Sạc: 66W có dây\",\"Camera\":\"Sau: Camera chính 50MP (có OIS), Góc siêu rộng 8MP, Macro 2MP  Trước: 50MP\"}', '', 'product_1763825267_6921d673330e3.png', 'vivo-v27', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:27:47'),
(13, 'Xiaomi 13', 1, 6479217.00, 0.00, 163, '', '{\"Màn hình\":\"AMOLED, 6.36 inch  Độ phân giải: 2400 × 1080 pixels (FHD+)  Tần số quét: 120Hz\",\"CPU\":\"Snapdragon 8 Gen 2 (4nm)\",\"RAM\":\"8GB / 12GB\",\"Ổ cứng\":\"128GB / 256GB / 512GB\",\"Pin\":\"Pin: 4500 mAh  Sạc: 67W có dây, 50W không dây\",\"Camera\":\"Sau: Camera chính 50MP (Leica), Góc siêu rộng 12MP, Tele 10MP  Trước: 32MP\"}', '', 'product_1763825280_6921d680acf8c.png', 'xiaomi-13', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:28:00'),
(14, 'Oppo Reno10', 1, 11554641.00, 0.00, 91, '', '{\"Màn hình\":\"AMOLED, 6.7 inch  Độ phân giải: 2412 × 1080 pixels (FHD+)  Tần số quét: 120Hz\",\"CPU\":\"Snapdragon 778G (6nm)\",\"RAM\":\"8GB / 12GB\",\"Ổ cứng\":\"128GB / 256GB\",\"Pin\":\"Pin: 5000 mAh  Sạc: 80W có dây\",\"Camera\":\"Sau: Camera chính 64MP, Góc siêu rộng 8MP, Tele 2MP  Trước: 32MP\"}', '', 'product_1763825292_6921d68cbd291.jpg', 'oppo-reno10', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:28:12'),
(15, 'iPhone 15', 1, 17746341.00, 0.00, 28, '', '{\"Màn hình\":\"Super Retina XDR OLED, 6.1 inch  Độ phân giải: 2556 × 1179 pixels  Tần số quét: 60Hz\",\"CPU\":\"Apple A16 Bionic (4nm)\",\"RAM\":\"6GB\",\"Ổ cứng\":\"128GB / 256GB / 512GB\",\"Pin\":\"Pin: 3349 mAh  Sạc: 20W có dây, 15W MagSafe\",\"Camera\":\"Sau: Camera chính 48MP, Camera góc siêu rộng 12MP  Trước: 12MP\"}', '', 'product_1763825303_6921d6970a854.jpg', 'iphone-15', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:28:23'),
(16, 'Realme 11', 1, 18185272.00, 0.00, 137, '', '{\"Màn hình\":\"Super AMOLED, 6.4 inch  Độ phân giải: 2400 × 1080 pixels (FHD+)  Tần số quét: 90Hz\",\"CPU\":\"MediaTek Helio G99 (6nm)\",\"RAM\":\"6GB / 8GB\",\"Ổ cứng\":\"128GB / 256GB\",\"Pin\":\"Pin: 5000 mAh  Sạc: 67W có dây\",\"Camera\":\"Sau: Camera chính 108MP, Góc siêu rộng 8MP, Macro 2MP  Trước: 16MP\"}', '', 'product_1763825311_6921d69f345f7.jpg', 'realme-11', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:28:31'),
(17, 'Samsung Galaxy S23', 1, 8376813.00, 0.00, 111, '', '{\"Màn hình\":\"Dynamic AMOLED 2X, 6.1 inch  Độ phân giải: 2340 × 1080 pixels (FHD+)  Tần số quét: 120Hz\",\"CPU\":\"Snapdragon 8 Gen 2 for Galaxy (4nm)\",\"RAM\":\"8GB\",\"Ổ cứng\":\"128GB / 256GB\",\"Pin\":\"Pin: 3900 mAh  Sạc: 25W có dây, 10W không dây\",\"Camera\":\"Sau: Camera chính 50MP, Góc siêu rộng 12MP, Tele 10MP  Trước: 12MP\"}', '', 'product_1763825345_6921d6c1e5e6f.png', 'samsung-galaxy-s23', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:29:05'),
(18, 'iPhone 14', 1, 5027937.00, 0.00, 119, '', '{\"Màn hình\":\"Super Retina XDR OLED, 6.1 inch  Độ phân giải: 2532 × 1170 pixels  Tần số quét: 60Hz\",\"CPU\":\"Apple A15 Bionic (5nm)\",\"RAM\":\"6GB\",\"Ổ cứng\":\"128GB / 256GB / 512GB\",\"Pin\":\"3279 mAh  Sạc 20W có dây, 15W MagSafe\",\"Camera\":\"Sau: Camera chính 12MP (OIS), Góc siêu rộng 12MP  Trước: 12MP  Tính năng: Cinematic Mode, Photonic Engine\"}', '', 'product_1763825326_6921d6ae5ed69.png', 'iphone-14', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:28:46'),
(19, 'Samsung Galaxy Z Fold5', 1, 22456789.00, 0.00, 45, '', '{\"Màn hình\":\"Màn ngoài: Dynamic AMOLED 2X, 6.2 inch  Màn trong: Dynamic AMOLED 2X, 7.6 inch  Độ phân giải: 2176 x 1812 pixels (màn trong)  Tần số quét: 120Hz\",\"CPU\":\"Snapdragon 8 Gen 2 for Galaxy (4nm)\",\"RAM\":\"12GB\",\"Ổ cứng\":\"256GB / 512GB / 1TB\",\"Pin\":\"4400 mAh  Sạc 25W có dây, 15W không dây  Wireless PowerShare\",\"Camera\":\"Camera sau:  Chính: 50MP (OIS)  Tele: 10MP (3x zoom quang)  Siêu rộng: 12MP  Camera selfie:  Màn trong: 4MP  Màn ngoài: 10MP\"}', '', 'product_1763825354_6921d6ca51a4a.png', 'samsung-galaxy-z-fold5', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:29:14'),
(20, 'Google Pixel 8', 1, 15678900.00, 0.00, 78, '', '{\"Màn hình\":\"Actua OLED, 6.2 inch  Độ phân giải: 2400 × 1080 pixels (FHD+)  Tần số quét: 120Hz  Độ sáng: 2000 nits (HDR)\",\"CPU\":\"Google Tensor G3 (4nm)\",\"RAM\":\"8GB\",\"Ổ cứng\":\"128GB / 256GB\",\"Pin\":\"4575 mAh  Sạc 27W có dây, 18W không dây (Qi)  Charge 50% trong 30 phút\",\"Camera\":\"Camera sau:  Chính: 50MP (Octa PD, OIS)  Siêu rộng: 12MP (autofocus)  Camera trước: 10.5MP  Tính năng: Magic Editor, Best Take, Night Sight\"}', '', 'product_1763825376_6921d6e08bf8e.png', 'google-pixel-8', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:29:36'),
(21, 'OnePlus 11', 1, 13456789.00, 0.00, 92, '', '{\"Màn hình\":\"Fluid AMOLED, 6.7 inch  Độ phân giải: 3216 × 1440 pixels (QHD+)  Tần số quét: 120Hz LTPO 3.0  Độ sáng: 1300 nits (HDR)\",\"CPU\":\"Snapdragon 8 Gen 2 (4nm)\",\"RAM\":\"16GB LPDDR5X\",\"Ổ cứng\":\"256GB UFS 4.0\",\"Pin\":\"5000 mAh  Sạc 100W SuperVOOC (1-100% trong 25 phút)  Không hỗ trợ sạc không dây\",\"Camera\":\"Camera sau:  Chính: 50MP Sony IMX890 (OIS)  Tele: 32MP (2x zoom quang)  Siêu rộng: 48MP  Camera trước: 16MP\"}', '', 'product_1763825384_6921d6e886956.png', 'oneplus-11', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:29:44'),
(22, 'Dell Inspiron', 2, 8088295.00, 0.00, 70, '', '{\"Màn hình\":\"15.6 inch FHD (1920 x 1080) IPS  Tần số quét: 60Hz\",\"CPU\":\"Intel Core i5-1235U (12th gen)\",\"RAM\":\"8GB DDR4 (nâng cấp đến 16GB)\",\"Ổ cứng\":\"512GB SSD NVMe\",\"Pin\":\"4-cell 54Wh  Thời gian sử dụng: ~8 giờ\",\"Card đồ hoa\":\"Tích hợp: Intel Iris Xe hoặc AMD Radeon Graphics  Rời (tuỳ chọn): NVIDIA GeForce MX550 2GB\"}', '', 'product_1763825398_6921d6f6768b7.jpg', 'dell-inspiron', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:29:58'),
(23, 'Asus Vivobook', 2, 12712202.00, 0.00, 63, '', '{\"Màn hình\":\"14 inch/15.6 inch FHD IPS  Tần số quét: 60Hz\",\"CPU\":\"Intel Core i3/i5 hoặc AMD Ryzen 3/5\",\"RAM\":\"8GB DDR4\",\"Ổ cứng\":\"512GB SSD NVMe\",\"Pin\":\"50Wh  Thời gian sử dụng: ~7-9 giờ\",\"Card đồ hoa\":\"Tích hợp: Intel UHD Graphics hoặc AMD Radeon Graphics  Rời (tuỳ chọn): NVIDIA GeForce MX350\"}', '', 'product_1763825405_6921d6fd95a55.jpg', 'asus-vivobook', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:05'),
(24, 'MacBook Air', 2, 9054968.00, 0.00, 130, '', '{\"Màn hình\":\"13.6 inch Liquid Retina, 2560 x 1664  Độ sáng 500 nits\",\"CPU\":\"Apple M2 (8-core CPU)\",\"RAM\":\"8GB/16GB/24GB unified memory\",\"Ổ cứng\":\"256GB/512GB/1TB/2TB SSD\",\"Pin\":\"52.6Wh  Thời gian sử dụng: ~15-18 giờ\",\"Card đồ hoa\":\"Tích hợp: Apple M2 (8-core/10-core GPU)\"}', '', 'product_1763825415_6921d70759a12.jpg', 'macbook-air', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:15'),
(25, 'HP Pavilion', 2, 19226664.00, 0.00, 7, '', '{\"Màn hình\":\"14 inch/15.6 inch FHD IPS\",\"CPU\":\"Intel Core i5-1240P hoặc AMD Ryzen 5 5625U\",\"RAM\":\"8GB/16GB DDR4\",\"Ổ cứng\":\"512GB SSD NVMe\",\"Pin\":\"43Wh  Thời gian sử dụng: ~6-7 giờ\",\"Card đồ hoa\":\"Tích hợp: Intel Iris Xe hoặc AMD Radeon Graphics  Rời (tuỳ chọn): NVIDIA GeForce GTX 1650\"}', '', 'product_1763825424_6921d7102b984.png', 'hp-pavilion', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:24'),
(26, 'Acer Aspire', 2, 9315312.00, 0.00, 106, '', '{\"Màn hình\":\"15.6 inch FHD IPS\",\"CPU\":\"Intel Core i5-1235U\",\"RAM\":\"8GB DDR4\",\"Ổ cứng\":\"512GB SSD NVMe\",\"Pin\":\"48Wh  Thời gian sử dụng: ~7 giờ\",\"Card đồ hoa\":\"Tích hợp: Intel Iris Xe Graphics  Rời (tuỳ chọn): NVIDIA GeForce RTX 2050\"}', '', 'product_1763825432_6921d7185af25.jpg', 'acer-aspire', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:32'),
(27, 'MacBook Pro', 2, 13616644.00, 0.00, 47, '', '{\"Màn hình\":\"14.2 inch Liquid Retina XDR, 3024 x 1964  Promotion 120Hz\",\"CPU\":\"Apple M3 (8-core CPU)\",\"RAM\":\"8GB/16GB/24GB unified memory\",\"Ổ cứng\":\"512GB/1TB/2TB SSD\",\"Pin\":\"70Wh  Thời gian sử dụng: ~18-22 giờ\",\"Card đồ hoa\":\"Tích hợp: Apple M3 (10-core GPU)\"}', '', 'product_1763825440_6921d72068b5a.png', 'macbook-pro', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:40'),
(28, 'Lenovo ThinkPad', 2, 14631232.00, 0.00, 45, '', '{\"Màn hình\":\"14 inch FHD IPS (1920 x 1200)\",\"CPU\":\"Intel Core i5-1335U\",\"RAM\":\"16GB DDR5\",\"Ổ cứng\":\"512GB SSD NVMe\",\"Pin\":\"52.5Wh  Thời gian sử dụng: ~10-12 giờ\",\"Card đồ hoa\":\"Tích hợp: Intel Iris Xe Graphics\"}', '', 'product_1763825447_6921d7274b65d.jpg', 'lenovo-thinkpad', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:47'),
(29, 'MSI Gaming', 2, 18765432.00, 0.00, 35, '', '{\"Màn hình\":\"15.6 inch FHD IPS, 144Hz\",\"CPU\":\"Intel Core i7-13650HX\",\"RAM\":\"16GB DDR5\",\"Ổ cứng\":\"1TB SSD NVMe\",\"Pin\":\"53.5Wh  Thời gian sử dụng: ~4-5 giờ (gaming)\",\"Card đồ hoa\":\"Rời: NVIDIA GeForce RTX 4060 8GB\"}', '', 'product_1763825453_6921d72d2a485.png', 'msi-gaming', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:53'),
(30, 'LG Gram', 2, 15678900.00, 0.00, 62, '', '{\"Màn hình\":\"16 inch WQXGA (2560 x 1600) IPS\",\"CPU\":\"Intel Core i5-1340P\",\"RAM\":\"16GB LPDDR5\",\"Ổ cứng\":\"512GB SSD NVMe\",\"Pin\":\"80Wh  Thời gian sử dụng: ~15-17 giờ\",\"Card đồ hoa\":\"Tích hợp: Intel Iris Xe Graphics\"}', '', 'product_1763825459_6921d733288d3.jpg', 'lg-gram', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:30:59'),
(31, 'iPad 9', 3, 1232774.00, 0.00, 66, '', '{\"Màn hình\":\"10.2 inch Retina LCD  Độ phân giải: 2160 x 1620 pixels\",\"CPU\":\"Apple A13 Bionic\",\"RAM\":\"3GB\",\"Ổ cứng\":\"64GB / 256GB  Loại: Bộ nhớ trong, không hỗ trợ thẻ nhớ mở rộng\",\"Pin\":\"32.4Wh  Sử dụng: ~10 giờ\"}', '', 'product_1763825476_6921d7441ce33.jpg', 'ipad-9', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:31:16'),
(32, 'Samsung Tab S9', 3, 3422795.00, 0.00, 42, '', '{\"Màn hình\":\"11 inch Dynamic AMOLED 2X  Độ phân giải: 2560 x 1600 pixels  Tần số quét: 120Hz\",\"CPU\":\"Snapdragon 8 Gen 2 for Galaxy\",\"RAM\":\"8GB / 12GB\",\"Ổ cứng\":\"128GB / 256GB  Mở rộng: Có, microSD lên đến 1TB\",\"Pin\":\"8400 mAh  Sạc nhanh 45W\"}', '', 'product_1763825483_6921d74ba921f.jpg', 'samsung-tab-s9', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:31:23'),
(33, 'iPad Air', 3, 3342246.00, 0.00, 50, '', '{\"Màn hình\":\"10.9 inch Liquid Retina  Độ phân giải: 2360 x 1640 pixels\",\"CPU\":\"Apple M1\",\"RAM\":\"8GB\",\"Ổ cứng\":\"64GB / 256GB  Loại: Bộ nhớ trong, không hỗ trợ thẻ nhớ\",\"Pin\":\"28.6Wh  Sử dụng: ~10 giờ\"}', '', 'product_1763825490_6921d752af6fd.png', 'ipad-air', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:31:30'),
(34, 'Lenovo Tab M10', 3, 6012345.00, 0.00, 96, '', '{\"Màn hình\":\"10.6 inch IPS LCD  Độ phân giải: 2000 x 1200 pixels\",\"CPU\":\"MediaTek Helio G80\",\"RAM\":\"4GB / 6GB\",\"Ổ cứng\":\"64GB / 128GB  Mở rộng: Có, microSD lên đến 1TB\",\"Pin\":\"7700 mAh  Sạc 20W\"}', '', 'product_1763825497_6921d75953386.png', 'lenovo-tab-m10', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:31:37'),
(35, 'iPad Pro', 3, 19640088.00, 0.00, 56, '', '{\"Màn hình\":\"12.9 inch Liquid Retina XDR (Mini-LED)  Độ phân giải: 2732 x 2048 pixels\",\"CPU\":\"Apple M2\",\"RAM\":\"8GB / 16GB\",\"Ổ cứng\":\"128GB / 256GB / 512GB / 1TB / 2TB  Loại: SSD, không hỗ trợ thẻ nhớ\",\"Pin\":\"40.88Wh  Sử dụng: ~10 giờ\"}', '', 'product_1763825504_6921d76033dfd.jpg', 'ipad-pro', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:31:44'),
(36, 'Xiaomi Pad 6', 3, 8765432.00, 0.00, 88, '', '{\"Màn hình\":\"11 inch IPS LCD, 144Hz  Độ phân giải: 2880 x 1800 pixels\",\"CPU\":\"Snapdragon 8+ Gen 1\",\"RAM\":\"8GB / 12GB\",\"Ổ cứng\":\"128GB / 256GB / 512GB  Loại: UFS 3.1, không hỗ trợ thẻ nhớ\",\"Pin\":\"8840 mAh  Sạc nhanh 67W\"}', '', 'product_1763825510_6921d7668fab3.jpg', 'xiaomi-pad-6', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:31:50'),
(37, 'Huawei MatePad', 3, 7654321.00, 0.00, 72, '', '{\"Màn hình\":\"11 inch OLED, 120Hz  Độ phân giải: 2560 x 1600 pixels\",\"CPU\":\"Snapdragon 888 4G\",\"RAM\":\"8GB / 12GB\",\"Ổ cứng\":\"128GB / 256GB / 512GB  Loại: UFS 3.1, hỗ trợ NM Card (lên đến 256GB)\",\"Pin\":\"8300 mAh  Sạc nhanh 66W (có dây), 40W (không dây)\"}', '', 'product_1763825519_6921d76f88805.jpg', 'huawei-matepad', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:31:59'),
(38, 'Camera WiFi', 4, 15248174.00, 0.00, 170, '', '{\"Độ phân giải:\":\"2K (2560 × 1440) hoặc 4MP (2688 × 1520)\",\"Công nghệ:\":\"Hồng ngoại ban đêm (tầm xa 10m)  Phát hiện chuyển động thông minh  Đàm thoại 2 chiều\",\"Kết nối:\":\"WiFi 2.4GHz  Hỗ trợ thẻ nhớ microSD (lên đến 128GB)\",\"Lắp đặt:\":\"Trong nhà/ngoài trời (một số model)  Xoay 360° (tuỳ model)\"}', '', 'product_1763825532_6921d77c382cf.png', 'camera-wifi', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:32:12'),
(39, 'Chuột không dây', 4, 2912917.00, 0.00, 20, '', '{\"Kết nối:\":\"USB Receiver 2.4GHz / Bluetooth 5.0  Khoảng cách: lên đến 10m\",\"Độ phân giải:\":\"1200 - 1600 DPI (tuỳ chỉnh)\",\"Pin:\":\"Pin AA hoặc sạc Li-ion  Thời gian sử dụng: 6 - 12 tháng (pin AA)\",\"Thiết kế:\":\"Ergonomic  4-6 nút bấm\"}', '', 'product_1763825539_6921d7838ff5c.jpg', 'chuot-khong-day', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:32:19'),
(40, 'Loa Soundbar', 4, 16425299.00, 0.00, 14, '', '{\"Công suất:\":\"100W - 400W (RMS)\",\"Kết nối:\":\"HDMI ARC, Optical, Bluetooth 5.0, USB  WiFi (một số model cao cấp)\",\"Tính năng:\":\"Subwoofer không dây (tuỳ model)  Hỗ trợ Dolby Audio / DTS Virtual:X  Nhiều chế độ âm thanh\",\"Kích thước:\":\"600 - 900mm (chiều dài)\"}', '', 'product_1763825546_6921d78a045ee.png', 'loa-soundbar', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:32:26'),
(41, 'Bàn phím cơ', 4, 7934377.00, 0.00, 169, '', '{\"Switch:\":\"Blue/Brown/Red (Outemu, Gateron, Cherry MX)\",\"Đèn LED:\":\"RGB 16.8 triệu màu\",\"Kết nối:\":\"USB Type-C / Bluetooth 5.1 (bàn phím không dây)  Dây rút / rời (tuỳ model)\",\"Tính năng:\":\"Anti-ghosting, N-key rollover  Phím điều khiển đa phương tiện\",\"Layout:\":\"68%, 87%, 100% phím\"}', '', 'product_1763825552_6921d79043d08.jpg', 'ban-phim-co', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:32:32'),
(42, 'Tai nghe Sony', 4, 3456789.00, 0.00, 120, '', '{\"Công nghệ:\":\"Chống ồi chủ động  8 micro xử lý tiếng ồi\",\"Pin:\":\"30 giờ (chống ồi bật)  Sạc nhanh 3 phút = 3 giờ nghe\",\"Kết nối:\":\"Bluetooth 5.2  NFC, Jack 3.5mm\",\"Tính năng:\":\"Touch control  Trợ lý ảo Google Assistant, Alexa\"}', '', 'product_1763825558_6921d796dc5d2.png', 'tai-nghe-sony', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:32:38'),
(43, 'Màn hình Dell', 4, 5678901.00, 0.00, 85, '', '{\"Kích thước:\":\"27 inch\",\"Độ phân giải:\":\"4K UHD (3840 × 2160)\",\"Công nghệ:\":\"IPS Black Panel  Độ sáng: 400 nits\",\"Kết nối:\":\"HDMI, DisplayPort, USB-C (90W)  USB Hub 4 cổng\",\"Tính năng:\":\"ComfortView Plus (giảm ánh sáng xanh)  Chân đế chỉnh độ cao, xoay\"}', '', 'product_1763825567_6921d79fc3225.jpg', 'man-hinh-dell', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:32:47'),
(44, 'Bếp từ đôi', 5, 7255945.00, 0.00, 147, '', '{\"Công suất:\":\"Tổng: 3600W - 4000W  Vùng nấu: 1800W - 2000W/vùng\",\"Tính năng:\":\"Điều khiển cảm ứng  Hẹn giờ từng vùng nấu  Khóa an toàn trẻ em  Tự động ngắt khi quá nhiệt\",\"Kích thước:\":\"700mm x 400mm (tiêu chuẩn âm bàn)\"}', '', 'product_1763825579_6921d7ab6ff11.jpg', 'bep-tu-doi', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:32:59'),
(45, 'Nồi chiên không dầu', 5, 18722546.00, 0.00, 32, '', '{\"Dung tích:\":\"5L - 8L (cho gia đình 4-6 người)\",\"Công suất:\":\"1500W - 1800W\",\"Tính năng:\":\"Điều chỉnh nhiệt: 80°C - 200°C  Hẹn giờ: 1 - 60 phút  Các chế độ nấu tự động\"}', '', 'product_1763825586_6921d7b28dc87.jpg', 'noi-chien-khong-dau', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:33:06'),
(46, 'Máy lạnh LG', 5, 12762176.00, 0.00, 116, '', '{\"Công suất làm lạnh:\":\"1.5 HP (~ 12,000 BTU)\",\"Công nghệ:\":\"Inverter tiết kiệm điện  Dual Inverter (một số model)  Chế độ làm lạnh nhanh\",\"Tính năng:\":\"Kháng khuẩn, khử mùi  Chế độ ngủ  Điều khiển từ xa & WiFi\"}', '', 'product_1763825593_6921d7b974c5f.jpg', 'may-lanh-lg', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:33:13'),
(47, 'Quạt điều hòa', 5, 11114418.00, 0.00, 193, '', '{\"Công suất:\":\"65W - 80W\",\"Dung tích bình nước:\":\"20L - 40L\",\"Tính năng:\":\"Làm mát bằng hơi nước  Bánh xe di chuyển  Điều khiển từ xa  Chế độ gió tự nhiên\"}', '', 'product_1763825601_6921d7c1b25ef.jpg', 'quat-dieu-hoa', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:33:21'),
(48, 'Máy lọc nước Karofi', 5, 6863469.00, 0.00, 148, '', '{\"Công nghệ lọc:\":\"8-9 lõi lọc (bao gồm RO)  Công suất lọc: 10-15 lít/giờ\",\"Tính năng:\":\"Màn hình hiển thị điện tử  Cảnh báo thay lõi  Tủ bảo vệ inox\",\"Tiêu chuẩn:\":\"Đạt QCVN6-1:2010/BYT\"}', '', 'product_1763825608_6921d7c8bcf25.jpg', 'may-loc-nuoc-karofi', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:33:28'),
(49, 'Máy giặt Toshiba', 5, 12345678.00, 0.00, 65, '', '{\"Công nghệ:\":\"Inverter tiết kiệm điện  Lồng giặt Diamond\",\"Tính năng:\":\"13 chế độ giặt  Giặt nước nóng  Tự động cân bằng\",\"Kết nối:\":\"Màn hình LED  Khóa trẻ em\"}', '', 'product_1763825616_6921d7d0a8ad2.png', 'may-giat-toshiba', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:33:36'),
(50, 'Tủ lạnh Samsung', 5, 15678900.00, 0.00, 42, '', '{\"Dung tích:\":\"550L - 650L\",\"Công nghệ:\":\"Digital Inverter  Làm đá nhanh Quick Cool\",\"Tính năng:\":\"Ngăn rau quả tươi lâu  Công nghệ kháng khuẩn  Màn hình cảm ứng\",\"Tiết kiệm điện:\":\"~ 1.1 kWh/ngày\"}', '', 'product_1763825624_6921d7d89a12e.png', 'tu-lanh-samsung', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:33:44'),
(51, 'Áo hoodie', 6, 13830608.00, 0.00, 76, '', '{\"Chất liệu:\":\"80% Cotton / 20% Polyester  Dày 320gsm\",\"Kiểu dáng:\":\"Form Regular fit  Cổ liền có dây rút  Túi kangaroo phía trước\",\"Kích thước:\":\"S, M, L, XL, XXL\",\"Màu sắc:\":\"Đen, Trắng, Xám, Xanh Navy\"}', '', 'product_1763825634_6921d7e2cc594.jpg', 'ao-hoodie', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:33:54'),
(52, 'Áo khoác bomber', 6, 14593284.00, 0.00, 149, '', '{\"Chất liệu:\":\"Vỏ ngoài: Polyester  Lót trong: Lưới thoáng khí\",\"Thiết kế:\":\"Cổ bẻ, Cài khóa kéo  Chun viền cổ tay & gấu áo  2 túi chéo 2 bên\",\"Kích thước:\":\"S, M, L, XL\",\"Màu sắc:\":\"Đen, Xanh rêu, Xám, Rằn ri\"}', '', 'product_1763825641_6921d7e939fb5.png', 'ao-khoac-bomber', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:34:01'),
(53, 'Giày sneaker', 6, 16679730.00, 0.00, 175, '', '{\"Chất liệu:\":\"Upper: Da tổng hợp / Vải mesh  Đế: Cao su non chống trượt\",\"Công nghệ:\":\"Đệm khí (Air Cushion)  Đế Ortholite kháng khuẩn\",\"Size:\":\"36 - 44 (có size lẻ)\",\"Trọng lượng:\":\"~350g/chiếc (size 40)\"}', '', 'product_1763825647_6921d7efbea30.jpg', 'giay-sneaker', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:34:07'),
(54, 'Áo thun nam', 6, 16985134.00, 0.00, 93, '', '{\"Chất liệu:\":\"100% Cotton Compact 2 chiều  Định lượng: 220 gsm\",\"Form áo:\":\"Form Regular  Cổ tròn\",\"Kích thước:\":\"S, M, L, XL, XXL\",\"Màu sắc:\":\"10 màu cơ bản\"}', '', 'product_1763825656_6921d7f81caa9.png', 'ao-thun-nam', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:34:16'),
(55, 'Quần jean', 6, 13072858.00, 0.00, 112, '', '{\"Chất liệu:\":\"98% Cotton / 2% Spandex  Dạng vải: Denim co giãn\",\"Kiểu dáng:\":\"Slim fit / Skinny / Regular  Dài ống: 98cm (size 30)\",\"Size:\":\"28 - 36 (vòng eo)\",\"Màu sắc:\":\"Xanh đậm, Xanh nhạt, Đen\"}', '', 'product_1763825673_6921d809296ac.jpg', 'quan-jean', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:34:33'),
(56, 'Váy đầm nữ', 6, 8765432.00, 0.00, 88, '', '{\"Chất liệu:\":\"Vải Kate / Chiffon / Voan\",\"Thiết kế:\":\"Dáng A / Dáng Ôm  Dài qua gối (dài 95cm)\",\"Size:\":\"S, M, L\",\"Màu sắc:\":\"Đen, Trắng, Hồng pastel, Xanh mint\"}', '', 'product_1763825678_6921d80eb4f60.jpg', 'vay-dam-nu', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:34:38'),
(57, 'Áo sơ mi nam', 6, 5678901.00, 0.00, 156, '', '{\"Chất liệu:\":\"100% Cotton Poplin  Định lượng: 120 gsm\",\"Form áo:\":\"Form Regular fit  Cổ ve\",\"Kích thước:\":\"S, M, L, XL, XXL\",\"Màu sắc:\":\"Trắng, Xanh than, Xanh nhạt, Sọc\"}', '', 'product_1763825685_6921d8152662a.jpg', 'ao-so-mi-nam', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:34:45'),
(58, 'Sữa rửa mặt', 7, 15027266.00, 0.00, 200, '', '{\"Thành phần:\":\"Salicylic Acid 2%  Chiết xuất Tràm Trà  Glycerin\",\"Loại da phù hợp:\":\"Da dầu, da mụn\",\"Độ pH:\":\"5.5 (cân bằng)\",\"Dung tích:\":\"150ml\"}', '', 'product_1763825697_6921d8210111e.jpg', 'sua-rua-mat', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:34:57'),
(59, 'Mặt nạ dưỡng ẩm', 7, 11293250.00, 0.00, 156, '', '{\"Thành phần:\":\"Hyaluronic Acid  Ceramide  Chiết xuất Nha Đam\",\"Thời gian đắp:\":\"15-20 phút\",\"Loại da:\":\"Mọi loại da, đặc biệt da khô\",\"Định lượng:\":\"25ml/túi\"}', '', 'product_1763825703_6921d827a74c7.jpg', 'mat-na-duong-am', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:35:03'),
(60, 'Serum vitamin C', 7, 5699671.00, 0.00, 119, '', '{\"Nồng độ:\":\"Vitamin C 20% (L-Ascorbic Acid)\",\"Thành phần phụ:\":\"Vitamin E  Ferulic Acid\",\"Bảo quản:\":\"Nhiệt độ phòng, tránh ánh sáng\",\"Dung tích:\":\"30ml\"}', '', 'product_1763825711_6921d82f62b6b.png', 'serum-vitamin-c', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:35:11'),
(61, 'Nước hoa nữ', 7, 9103273.00, 0.00, 168, '', '{\"Nhóm hương:\":\"Hương Hoa Cỏ\",\"Lưu hương:\":\"6-8 giờ\",\"Dung tích:\":\"50ml / 100ml\",\"Nồng độ:\":\"EDP (15-20%)\"}', '', 'product_1763825718_6921d836a5b92.jpg', 'nuoc-hoa-nu', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:35:18'),
(62, 'Kem dưỡng da', 7, 8158885.00, 0.00, 121, '', '{\"Kết cấu:\":\"Gel-Cream\",\"Thành phần:\":\"Niacinamide 5%  Peptide  Chiết xuất Rễ Cam Thảo\",\"Loại da:\":\"Da dầu, da hỗn hợp\",\"Dung tích:\":\"50ml\"}', '', 'product_1763825725_6921d83d8259d.jpg', 'kem-duong-da', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:35:25'),
(63, 'Son môi', 7, 345678.00, 0.00, 200, '', '{\"Thành phần:\":\"Dầu Argan  Vitamin E  Không chì\",\"Độ bền:\":\"8-10 giờ\",\"Trọng lượng:\":\"3.5g\",\"Màu sắc:\":\"12 tone màu\"}', '', 'product_1763825733_6921d845cbf54.jpg', 'son-moi', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:35:33'),
(64, 'Kem chống nắng', 7, 456789.00, 0.00, 180, '', '{\"Chỉ số:\":\"SPF 50+ / PA++++\",\"Kết cấu:\":\"Sữa (Milky)\",\"Thành phần:\":\"Aqua Max Technology  Không cồn\",\"Dung tích:\":\"50ml\"}', '', 'product_1763825740_6921d84ca53a3.jpg', 'kem-chong-nang', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:35:40'),
(65, 'Xe đẩy trẻ em', 8, 15607038.00, 0.00, 179, '', '{\"Trọng lượng:\":\"8.5kg\",\"Tải trọng:\":\"Lên đến 15kg\",\"Chất liệu:\":\"Khung hợp kim nhôm  Vải dù chống thấm\",\"Tính năng:\":\"Gập gọn 1 bước  5 điểm an toàn  Lốp cao su hơi\",\"Độ tuổi:\":\"0 - 36 tháng\"}', '', 'product_1763825751_6921d857a3331.jpg', 'xe-day-tre-em', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:35:51'),
(66, 'Đồ chơi lắp ráp', 8, 18251510.00, 0.00, 79, '', '{\"Chất liệu:\":\"Nhựa ABS nguyên sinh an toàn\",\"Số chi tiết:\":\"250 - 500 mảnh\",\"Độ tuổi:\":\"4 - 8 tuổi\",\"Kích thước hộp:\":\"25 x 35 x 8 cm\"}', '', 'product_1763825760_6921d860c46cb.jpg', 'do-choi-lap-rap', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:36:00'),
(67, 'Sữa bột cho bé', 8, 10899219.00, 0.00, 47, '', '{\"Độ tuổi:\":\"6 - 12 tháng\",\"Thành phần:\":\"DHA, ARA, Choline  Prebiotics (FOS/GOS)  13 Vitamin & 9 Khoáng chất\",\"Khối lượng:\":\"900g/hộp\",\"Xuất xứ:\":\"Nhập khẩu từ Châu Âu\"}', '', 'product_1763825766_6921d866ca3cf.jpg', 'sua-bot-cho-be', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:36:06'),
(68, 'Nôi gỗ cho bé', 8, 18207197.00, 0.00, 82, '', '{\"Chất liệu:\":\"Gỗ thông tự nhiên\",\"Kích thước:\":\"Dài 120cm x Rộng 60cm\",\"Tải trọng:\":\"25kg\",\"Tính năng:\":\"3 mức điều chỉnh độ cao Có bánh xe di chuyển\"}', '', 'product_1763825772_6921d86c9398a.jpg', 'noi-go-cho-be', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:36:12'),
(69, 'Bỉm Merries', 8, 4114161.00, 0.00, 32, '', '{\"Kích cỡ:\":\"M (6-11kg)  L (9-14kg)  XL (12-22kg)\",\"Thiết kế:\":\"3 lớp thấm hút  Bề mặt mềm mại\",\"Định lượng:\":\"56 miếng/túi\",\"Xuất xứ:\":\"Nhật Bản\"}', '', 'product_1763825794_6921d882acbb3.jpg', 'bim-merries', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:36:34'),
(70, 'Bình sữa', 8, 234567.00, 0.00, 150, '', '{\"Chất liệu:\":\"PPSU chịu nhiệt 180°C\",\"Dung tích:\":\"240ml\",\"Tính năng:\":\"Van thông khí chống sặc  Núm ty silicone mềm\",\"Độ tuổi:\":\"0 - 12 tháng\"}', '', 'product_1763825801_6921d8898fba3.png', 'binh-sua', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:36:41'),
(71, 'Tủ quần áo', 9, 16727908.00, 0.00, 168, '', '{\"Chất liệu:\":\"Gỗ MDF phủ melamine chống ẩm  Chân inox\",\"Kích thước:\":\"Cao 200cm x Rộng 120cm x Sâu 55cm\",\"Thiết kế:\":\"2 cánh mở  3 ngăn treo + 2 ngăn kéo  Chịu tải: 50kg\",\"Màu sắc:\":\"Vân gỗ sáng, Vân gỗ tối, Trắng\"}', '', 'product_1763825810_6921d892a1b99.jpg', 'tu-quan-ao', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:36:50'),
(72, 'Đèn trang trí', 9, 8766471.00, 0.00, 150, '', '{\"Chất liệu:\":\"Thân: Kim loại mạ đồng  Chụp: Thủy tinh mờ\",\"Kích thước:\":\"Đường kính 40cm x Cao 30cm\",\"Bóng đèn:\":\"3 x E27 (Max 9W/bóng)  LED vàng ấm 2700K\",\"Công suất:\":\"27W\"}', '', 'product_1763825819_6921d89b88174.jpg', 'den-trang-tri', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:36:59'),
(73, 'Bàn gỗ thông', 9, 12210050.00, 0.00, 138, '', '{\"Chất liệu:\":\"100% gỗ thông tự nhiên  Xử lý chống mối mọt\",\"Kích thước:\":\"Dài 140cm x Rộng 70cm x Cao 75cm\",\"Tải trọng:\":\"80kg\",\"Phụ kiện:\":\"Kèm 2 ngăn kéo\",\"Màu sắc:\":\"Màu gỗ tự nhiên phủ vecni\"}', '', 'product_1763825825_6921d8a1dfd24.jpg', 'ban-go-thong', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:05'),
(74, 'Ghế sofa', 9, 5857341.00, 0.00, 80, '', '{\"Chất liệu:\":\"Khung: Gỗ xà cừ  Đệm mút cao su non  Vải bọc: Vải bố chống bám bụi\",\"Kích thước:\":\"Dài 200cm x Sâu 90cm x Cao 85cm\",\"Số chỗ:\":\"3 chỗ\",\"Tải trọng:\":\"250kg\",\"Màu sắc:\":\"Xám, Be, Xanh navy\"}', '', 'product_1763825832_6921d8a82a3ab.jpg', 'ghe-sofa', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:12'),
(75, 'Rèm cửa', 9, 2345678.00, 0.00, 95, '', '{\"Chất liệu:\":\"Vải voan Hàn Quốc  Có lớp cản nhiệt\",\"Kích thước:\":\"Rộng 150cm x Cao 250cm/tấm\",\"Tính năng:\":\"Chống tia UV 80%  Giảm nhiệt 3-5°C\",\"Màu sắc:\":\"Trắng sữa, Kem, Xám nhạt\"}', '', 'product_1763825839_6921d8af68a5c.jpg', 'rem-cua', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:19'),
(76, 'Xe đạp thể thao', 10, 1825697.00, 0.00, 36, '', '{\"Khung xe:\":\"Hợp kim nhôm 6061  Size: 26 inch\",\"Hệ thống chuyển động:\":\"21 tốc độ (Shimano)  Phanh đĩa cơ\",\"Trọng lượng:\":\"14.5 kg\",\"Tải trọng:\":\"120 kg\",\"Đối tượng:\":\"Người lớn, thanh thiếu niên\"}', '', 'product_1763825849_6921d8b97640e.jpg', 'xe-dap-the-thao', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:29'),
(77, 'Găng tay tập gym', 10, 3279197.00, 0.00, 79, '', '{\"Chất liệu:\":\"Da PU siêu bền  Lót lòng bàn tay: Đệm silicone\",\"Thiết kế:\":\"Cổ tay có khóa dán  Ngón tay hở\",\"Kích cỡ:\":\"S, M, L, XL\",\"Màu sắc:\":\"Đen, Xám, Đỏ\"}', '', 'product_1763825856_6921d8c035e39.jpg', 'gang-tay-tap-gym', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:36'),
(78, 'Tạ tay 5kg', 10, 1096332.00, 0.00, 136, '', '{\"Chất liệu:\":\"Lõi gang, bọc nhựa PVC\",\"Kích thước:\":\"Dài 25cm, Đường kính 8cm\",\"Trọng lượng:\":\"5kg / quả (có các mức 1kg, 2kg, 3kg, 5kg)\",\"Màu sắc:\":\"Xanh, Đen, Hồng, Tím\"}', '', 'product_1763825863_6921d8c7146dd.jpg', 'ta-tay-5kg', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:43'),
(79, 'Thảm yoga', 10, 14131550.00, 0.00, 94, '', '{\"Chất liệu:\":\"PVC xốp cao cấp  Độ dày: 6mm\",\"Kích thước:\":\"183cm x 61cm\",\"Tính năng:\":\"Chống trượt 2 mặt  Cách nhiệt, êm ái\",\"Trọng lượng:\":\"1.2 kg\"}', '', 'product_1763825869_6921d8cd076ee.jpg', 'tham-yoga', 0, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:49'),
(80, 'Giày chạy bộ', 10, 9510998.00, 0.00, 6, '', '{\"Công nghệ:\":\"Đệm khí Air tại gót chân  Đế giữa bằng EVA\",\"Chất liệu:\":\"Upper: Mesh thoáng khí  Đế ngoài: Cao su tự nhiên\",\"Size:\":\"38 - 44 (có size lẻ)\",\"Trọng lượng:\":\"250g/chiếc (size 40)\"}', '', 'product_1763825877_6921d8d512f7b.png', 'giay-chay-bo', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:37:57'),
(81, 'Máy chạy bộ', 10, 12345678.00, 0.00, 25, '', '{\"Động cơ:\":\"DC 2.5 HP\",\"Tốc độ:\":\"1 - 14 km/h\",\"Kích thước vùng chạy:\":\"120 x 42 cm\",\"Chức năng:\":\"12 chương trình tập  Màn hình LCD hiển thị\",\"Tải trọng:\":\"120 kg\"}', '', 'product_1763825882_6921d8daf0f48.jpg', 'may-chay-bo', 1, 1, '2025-11-21 05:31:59', '2025-11-22 15:38:02'),
(82, 'Tiểu thuyết', 12, 690904.00, 0.00, 71, '', '{\"Tác giả:\":\"Victor Hugo\",\"Thông tin xuất bản:\":\"Nhà xuất bản: Văn Học  Năm XB: 2022  Số trang: 1,232  Ngôn ngữ: Tiếng Việt\",\"Định dạng:\":\"Bìa mềm  Kích thước: 14 x 20.5 cm\"}', '', 'product_1763825893_6921d8e54f032.jpg', 'tieu-thuyet', 1, 1, '2025-11-21 05:31:59', '2025-11-25 14:30:01'),
(83, 'Sách tiếng Anh', 12, 14935728.00, 0.00, 150, '', '{\"Tác giả:\":\"Raymond Murphy\",\"Thông tin xuất bản:\":\"Nhà xuất bản: Cambridge  Ấn bản: 5th Edition  Số trang: 380  Ngôn ngữ: Tiếng Anh\",\"Cấp độ:\":\"Intermediate (B1-B2)\"}', '', 'product_1763825900_6921d8ecd805c.png', 'sach-tieng-anh', 1, 1, '2025-11-21 05:31:59', '2025-11-25 14:29:25'),
(84, 'Sách kỹ năng sống', 12, 8648562.00, 0.00, 54, '', '{\"Tác giả:\":\"Dale Carnegie\",\"Thông tin xuất bản:\":\"Nhà xuất bản: Tổng Hợp TPHCM  Số trang: 320  Ngôn ngữ: Tiếng Việt\",\"Chủ đề:\":\"Phát triển bản thân  Kỹ năng giao tiếp\"}', '', 'product_1763825907_6921d8f36ecf0.jpg', 'sach-ky-nang-song', 0, 1, '2025-11-21 05:31:59', '2025-11-25 14:28:52'),
(85, 'Sách lập trình Python', 12, 14948518.00, 0.00, 162, '', '{}', '', 'product_1763825913_6921d8f9a3d49.jpg', 'sach-lap-trinh-python', 0, 1, '2025-11-21 05:31:59', '2025-11-25 14:21:59'),
(86, 'Sách giáo khoa', 12, 15267165.00, 0.00, 73, '', '{\"Bộ sách:\":\"Kết nối tri thức với cuộc sống\",\"Thông tin xuất bản:\":\"Nhà xuất bản: Giáo Dục Việt Nam  Năm xuất bản: 2022  Số trang: 156  Ngôn ngữ: Tiếng Việt\",\"Nội dung chính:\":\"Ứng dụng tin học  Lập trình cơ bản  Mạng máy tính và Internet\",\"Định dạng:\":\"Bìa mềm  Kích thước: 17 x 24 cm\"}', '', 'product_1763825920_6921d900e7e20.jpg', 'sach-giao-khoa', 0, 1, '2025-11-21 05:31:59', '2025-11-25 14:28:13'),
(87, 'Truyện tranh', 12, 234567.00, 0.00, 200, '', '{\"Tác giả:\":\"Fujiko F. Fujio\",\"Thông tin xuất bản:\":\"Nhà xuất bản: Kim Đồng  Tập: 1-45  Ngôn ngữ: Tiếng Việt\",\"Định dạng:\":\"Bìa mềm  Khổ: 12.5 x 18 cm\"}', '', 'product_1763825926_6921d90697517.jpg', 'truyen-tranh', 1, 1, '2025-11-21 05:31:59', '2025-11-25 14:27:29'),
(88, 'Bánh quy Danisa', 11, 18811877.00, 0.00, 27, '', '{\"Thành phần:\":\"Bột mì, bơ sữa 25%, đường, sữa bột  Hàm lượng bơ: ≥25%\",\"Quy cách đóng gói:\":\"Khối lượng tịnh: 454g/hộp  Số lượng: ~36-40 bánh/hộp\",\"Hạn sử dụng:\":\"12 tháng\",\"Xuất xứ:\":\"Indonesia / Malaysia\"}', '', 'product_1763825935_6921d90f5ae15.jpg', 'banh-quy-danisa', 0, 1, '2025-11-21 05:31:59', '2025-11-25 14:23:36'),
(89, 'Cafe Hòa Tan G7 3-in-1', 11, 15315270.00, 0.00, 160, '', '{}', '', 'product_1763825941_6921d9153bd1f.jpg', 'cafe-hoa-tan-g7-3in1', 0, 1, '2025-11-21 05:31:59', '2025-11-25 14:23:44'),
(90, 'Gạo thơm', 11, 5258515.00, 0.00, 181, '', '{\"Chủng loại:\":\"Gạo thơm cao cấp ST25\",\"Quy cách đóng gói:\":\"Khối lượng: 5kg/bao\",\"Hàm lượng dinh dưỡng:\":\"Carbohydrate: 77%  Protein: 7.5%  Lipid: 1.2%\",\"Hạn sử dụng:\":\"12 tháng\",\"Xuất xứ:\":\"Việt Nam\"}', '', 'product_1763825947_6921d91b300a9.jpg', 'gao-thom', 1, 1, '2025-11-21 05:31:59', '2025-11-25 14:24:27'),
(91, 'Nước ngọt lon 7up', 11, 9189002.00, 0.00, 95, '', '{\"Thành phần:\":\"Nước, đường, acid citric, hương chanh tự nhiên  Hàm lượng đường: 10.2g/100ml\",\"Quy cách đóng gói:\":\"Dung tích: 330ml/lon  Đóng gói: Thùng 24 lon\",\"Hạn sử dụng:\":\"9 tháng\",\"Xuất xứ:\":\"Việt Nam\"}', '', 'product_1763825952_6921d920de124.jpg', 'nuoc-ngot-lon-7up', 0, 1, '2025-11-21 05:31:59', '2025-11-25 14:25:14'),
(92, 'Mì gói hảo hảo', 11, 12624849.00, 0.00, 12, '', '{\"Thành phần:\":\"Vắt mì, gói gia vị, dầu gia vị  Khối lượng: 65g/gói\",\"Hương vị:\":\"Chua cay / Tôm chua cay\",\"Hạn sử dụng:\":\"6 tháng\",\"Xuất xứ:\":\"Việt Nam\"}', '', 'product_1763825959_6921d927822d3.jpg', 'mi-goi-hao-hao', 1, 1, '2025-11-21 05:31:59', '2025-11-25 14:25:52'),
(93, 'Dầu ăn', 11, 345678.00, 0.00, 150, '', '{\"Thành phần:\":\"100% dầu hạt cải tinh luyện\",\"Quy cách đóng gói:\":\"Dung tích: 1 lít/chai\",\"Chỉ số dinh dưỡng:\":\"Cholesterol: 0mg  Chất béo bão hòa: 7%\",\"Hạn sử dụng:\":\"24 tháng\",\"Xuất xứ:\":\"Việt Nam\"}', '', 'product_1763825965_6921d92d838cc.jpg', 'dau-an', 0, 1, '2025-11-21 05:31:59', '2025-11-25 14:26:32'),
(94, 'Trà túi lọc', 11, 456789.00, 0.00, 200, '', '{\"Thành phần:\":\"100% trà đen\",\"Quy cách đóng gói:\":\"Khối lượng: 2g/túi  Đóng gói: Hộp 25 túi lọc\",\"Hạn sử dụng:\":\"24 tháng\",\"Xuất xứ:\":\"Việt Nam\"}', '', 'product_1763825972_6921d93416921.jpg', 'tra-tui-loc', 1, 1, '2025-11-21 05:31:59', '2025-11-25 14:15:03');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `category` varchar(50) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `category`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'website_info', 'site_name', 'ShopNets', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(2, 'website_info', 'site_tagline', 'Your Online Shopping Destination', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(3, 'website_info', 'meta_description', 'ShopNets - Nền tảng mua sắm trực tuyến hàng đầu với hàng ngàn sản phẩm chất lượng cao và dịch vụ tốt nhất.', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(4, 'website_info', 'site_keywords', 'mua sắm, online shopping, thời trang, điện tử', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(5, 'contact_info', 'contact_email', 'admin@shopnets.com', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(6, 'contact_info', 'support_email', 'support@shopnets.com', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(7, 'contact_info', 'contact_phone', '0123-456-789', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(8, 'contact_info', 'contact_hotline', '1900-1234', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(9, 'contact_info', 'contact_address', '123 Đường ABC, Phường XYZ, Quận 1, TP. Hồ Chí Minh, Việt Nam', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(10, 'contact_info', 'working_hours', '8:00 - 17:00, Thứ 2 - Chủ nhật', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(11, 'contact_info', 'website', 'https://shopnets.com', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(12, 'system_settings', 'currency', 'VND', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(13, 'system_settings', 'timezone', 'Asia/Ho_Chi_Minh', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(14, 'system_settings', 'language', 'vi', '2025-11-08 17:13:08', '2025-11-08 17:13:08'),
(15, 'system_settings', 'date_format', 'd/m/Y', '2025-11-08 17:13:08', '2025-11-08 17:13:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `full_name`, `phone`, `address`, `avatar`, `password`, `reset_token`, `reset_token_expires`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'john_doe', 'john@example.com', NULL, NULL, NULL, NULL, '6e0b7076126a29d5dfcbd54835387b7b', NULL, NULL, 'user', 'active', '2025-11-07 16:42:57', '2025-11-07 16:42:57'),
(2, 'sarah_johnson', 'sarah@example.com', NULL, '0902345678', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(3, 'mike_brown', 'mike@example.com', NULL, '0903456789', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(4, 'emily_davis', 'emily@example.com', NULL, '0904567890', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(5, 'david_wilson', 'david@example.com', NULL, '0905678901', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(6, 'TuanNguyen', 'tungnguyenbt298@gmail.com', 'Nguyen Tuan', '0984831424', '495/Quốc Lộ 13', NULL, '$2y$10$nW2btTt1vdvBm4j1o8ZK5ec8kMotg6hd.ev8xqorohps0VrURnTAC', NULL, NULL, 'user', 'active', '2025-11-17 09:47:07', '2025-11-17 09:47:07'),
(7, 'test1', 'test1@gmail.comte', 'test1', '123', '12412', NULL, '$2y$10$tnWYUhOqQQj8GKLX3MiYLOkphA8raSHIxAXLTB0MUklfe5r8wmWLS', NULL, NULL, 'user', 'active', '2025-11-26 06:03:22', '2025-11-26 06:03:22'),
(8, 'test', 'testnha@gmail.com', 'testna3123', '123', '22 lkajwp', NULL, '$2y$10$gEEA.b6GNMNtHBSfLF/2/uDKgJGuuL1rexjF.DNryw/vALIyHihky', NULL, NULL, 'user', 'active', '2025-11-26 06:24:02', '2025-11-26 14:19:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_categories_name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Indexes for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_history_order` (`order_id`),
  ADD KEY `idx_status_history_created` (`created_at`);

--
-- Indexes for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_image` (`image`),
  ADD KEY `idx_products_category_id` (`category_id`),
  ADD KEY `idx_products_slug` (`slug`),
  ADD KEY `idx_products_featured` (`featured`),
  ADD KEY `idx_products_is_active` (`is_active`),
  ADD KEY `idx_products_created_at` (`created_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_setting` (`category`,`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `payment_logs`
--
ALTER TABLE `payment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=260;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_logs`
--
ALTER TABLE `payment_logs`
  ADD CONSTRAINT `payment_logs_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
