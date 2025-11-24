-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 18, 2025 lúc 03:50 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shopnets`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `admin`
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
-- Đang đổ dữ liệu cho bảng `admin`
--

INSERT INTO `admin` (`id`, `email`, `phone`, `avatar`, `password`, `role`, `created_at`) VALUES
(1, 'admin@shopnet.com', '0984831424', 'assets/images/uploads/avatar_1763086702_6916916eb2f2e.jpg', '$2y$10$M5VccEUAL6xamNY.YL5kzOYwLl.D6TfQnHfJNPqst33hkONPkMzPe', 'admin', '2025-11-01 08:27:30');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Smartphone',     'Điện thoại thông minh và thiết bị di động',                           '2025-11-07 15:11:41', '2025-11-19 12:00:00'),
(2, 'Laptop',         'Máy tính xách tay và phụ kiện',                                       '2025-11-07 15:11:41', '2025-11-19 12:00:00'),
(3, 'Tablet',         'Máy tính bảng và phụ kiện',                                           '2025-11-07 15:11:41', '2025-11-19 12:00:00'),
(4, 'Headphones',     'Tai nghe và thiết bị âm thanh',                                       '2025-11-07 15:11:41', '2025-11-19 12:00:00'),
(5, 'Smartwatch',     'Đồng hồ thông minh và thiết bị đeo thông minh',                       '2025-11-07 15:11:41', '2025-11-19 12:00:00'),
(6, 'Computer',       'Máy tính để bàn, PC và linh kiện máy tính',                           '2025-11-19 12:00:00', '2025-11-19 12:00:00'),
(7, 'GameConsole',   'Máy chơi game console (PlayStation, Xbox, Nintendo...) và phụ kiện',  '2025-11-19 12:00:00', '2025-11-19 12:00:00'),
(8, 'Camera',         'Máy ảnh, máy quay phim và phụ kiện nhiếp ảnh',                        '2025-11-19 12:00:00', '2025-11-19 12:00:00'),
(9, 'Television',     'Tivi thông minh, màn hình TV các loại',                               '2025-11-19 12:00:00', '2025-11-19 12:00:00'),
(10,'Accessories',    'Phụ kiện công nghệ: ốp lưng, sạc, cáp, giá đỡ, loa di động, chuột, bàn phím...', '2025-11-19 12:00:00', '2025-11-19 12:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
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
-- Đang đổ dữ liệu cho bảng `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_email`, `customer_name`, `customer_phone`, `shipping_address`, `billing_address`, `subtotal`, `shipping_fee`, `tax_amount`, `discount_amount`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `shipping_method`, `tracking_number`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-001', NULL, 'john@example.com', 'John Smith', '0901234567', '123 Main St, District 1, Ho Chi Minh City', '123 Main St, District 1, Ho Chi Minh City', 31000000.00, 50000.00, 0.00, 0.00, 31050000.00, 'cod', 'pending', 'shipped', 'Standard Shipping', NULL, 'Customer requested fast delivery', '2024-11-10 03:30:00', '2024-11-11 07:20:00'),
(2, 'ORD-002', 2, 'sarah@example.com', 'Sarah Johnson', '0902345678', '456 Oak Ave, District 3, Ho Chi Minh City', '456 Oak Ave, District 3, Ho Chi Minh City', 6000000.00, 30000.00, 0.00, 0.00, 6030000.00, 'bank_transfer', 'paid', 'pending', 'Express Shipping', NULL, NULL, '2024-11-11 08:45:00', '2024-11-11 08:45:00'),
(3, 'ORD-003', 3, 'mike@example.com', 'Mike Brown', '0903456789', '789 Pine St, District 7, Ho Chi Minh City', '789 Pine St, District 7, Ho Chi Minh City', 28000000.00, 50000.00, 0.00, 0.00, 28050000.00, 'vnpay', 'refunded', 'cancelled', 'Standard Shipping', NULL, 'Product was damaged during shipping', '2024-11-09 04:20:00', '2024-11-12 02:30:00'),
(4, 'ORD-004', 4, 'emily@example.com', 'Emily Davis', '0904567890', '321 Elm St, District 2, Ho Chi Minh City', '321 Elm St, District 2, Ho Chi Minh City', 12000000.00, 40000.00, 0.00, 0.00, 12040000.00, 'momo', 'paid', 'shipped', 'Express Shipping', NULL, NULL, '2024-11-12 02:15:00', '2024-11-13 09:40:00'),
(5, 'ORD-005', 5, 'david@example.com', 'David Wilson', '0905678901', '654 Maple Ave, District 5, Ho Chi Minh City', '654 Maple Ave, District 5, Ho Chi Minh City', 31000000.00, 50000.00, 0.00, 0.00, 31050000.00, 'cod', 'pending', 'pending', 'Standard Shipping', NULL, 'Customer will pay on delivery', '2024-11-13 09:30:00', '2024-11-13 09:30:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attributes`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `order_status_history`
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
(13, 5, 'pending', 'Order placed successfully', '2024-11-13 09:30:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `compare_price` decimal(12,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `specifications` JSON DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `products`
--

INSERT INTO `products` (`id`, `name`, `category_id`, `price`, `compare_price`, `quantity`, `specifications`, `description`, `short_description`, `image`, `slug`, `featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 15 Pro', 1, 10000000.00, 0.00, 100, NULL, '', '', 'product_1763382757_691b15e534b83.jpg', 'iphone-15-pro', 1, 1, '2025-11-17 12:11:41', '2025-11-18 14:45:47');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
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
-- Đang đổ dữ liệu cho bảng `settings`
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
-- Cấu trúc bảng cho bảng `users`
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
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `full_name`, `phone`, `address`, `avatar`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'john_doe', 'john@example.com', NULL, NULL, NULL, NULL, '6e0b7076126a29d5dfcbd54835387b7b', 'user', 'active', '2025-11-07 16:42:57', '2025-11-07 16:42:57'),
(2, 'sarah_johnson', 'sarah@example.com', NULL, '0902345678', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(3, 'mike_brown', 'mike@example.com', NULL, '0903456789', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(4, 'emily_davis', 'emily@example.com', NULL, '0904567890', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(5, 'david_wilson', 'david@example.com', NULL, '0905678901', NULL, NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'active', '2025-11-14 11:38:52', '2025-11-14 11:38:52'),
(6, 'TuanNguyen', 'tungnguyenbt298@gmail.com', 'Nguyen Tuan', '0984831424', '495/Quốc Lộ 13', NULL, '$2y$10$nW2btTt1vdvBm4j1o8ZK5ec8kMotg6hd.ev8xqorohps0VrURnTAC', 'user', 'active', '2025-11-17 09:47:07', '2025-11-17 09:47:07');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_categories_name` (`name`);

--
-- Chỉ mục cho bảng `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`order_status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- Chỉ mục cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Chỉ mục cho bảng `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status_history_order` (`order_id`),
  ADD KEY `idx_status_history_created` (`created_at`);

--
-- Chỉ mục cho bảng `products`
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
-- Chỉ mục cho bảng `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_setting` (`category`,`setting_key`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT cho bảng `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD CONSTRAINT `order_status_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
