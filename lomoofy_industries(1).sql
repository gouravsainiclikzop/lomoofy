-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 12, 2025 at 01:59 PM
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
-- Database: `lomoofy_industries`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `description`, `logo`, `website`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(19, 'GRUNT', 'grunt', NULL, 'brands/vqZRf9vtMIqADXSEglkcTIWvL1QXaO6BeRverU3P.webp', NULL, 1, 1, '2025-12-02 01:33:42', '2025-12-02 03:25:58'),
(20, 'Sayuri Designer', 'sayuri-designer', NULL, 'brands/y3bI5ReiPRNJ0yVOVmNBxz5OM7a8r1bsC3wrMOVD.webp', NULL, 1, 2, '2025-12-02 03:24:32', '2025-12-02 03:24:59'),
(21, 'Adorify', 'adorify', NULL, 'brands/tMEYlPHfGLkYGibLu0iKMAMmQzhwBzyC6BfpktCt.png', NULL, 1, 0, '2025-12-02 03:25:37', '2025-12-02 03:25:37'),
(22, 'Other', 'other', 'Generic brand for unbranded products', NULL, NULL, 1, 0, '2025-12-02 03:26:13', '2025-12-02 03:26:13');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-storage_refresh_check', 'b:0;', 1765544088);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `coupon_code` varchar(255) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `session_id`, `customer_id`, `coupon_code`, `subtotal`, `tax_amount`, `shipping_amount`, `discount_amount`, `total_amount`, `expires_at`, `created_at`, `updated_at`) VALUES
(3, 'xxxddd', NULL, NULL, 0.00, 0.00, 50.00, 0.00, 50.00, '2025-12-25 05:14:59', '2025-11-25 05:14:59', '2025-11-25 05:14:59');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `reserved_stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `parent_id`, `image`, `is_active`, `featured`, `sort_order`, `meta_title`, `meta_description`, `meta_keywords`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Apparel', 'apparel', 'Clothing and apparel', NULL, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-11-27 02:03:20', '2025-12-02 00:55:21', '2025-12-02 00:55:21'),
(2, 'Footwear', 'footwear', 'Shoes and footwear', NULL, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-11-27 02:03:20', '2025-12-02 00:55:21', '2025-12-02 00:55:21'),
(3, 'Electronics', 'electronics', 'Electronic devices', NULL, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-11-27 02:03:20', '2025-12-02 00:55:21', '2025-12-02 00:55:21'),
(4, 'Jewelry', 'jewelry', 'Jewelry and accessories', NULL, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-11-27 02:03:20', '2025-12-02 00:55:21', '2025-12-02 00:55:21'),
(5, 'Accessories', 'accessories', 'Fashion accessories', NULL, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-11-27 02:03:20', '2025-12-02 00:55:32', '2025-12-02 00:55:32'),
(6, 'Sports & Outdoors', 'sports-outdoors', 'Sports and outdoor equipment', NULL, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-11-27 02:03:20', '2025-12-02 00:55:21', '2025-12-02 00:55:21'),
(7, 'Men', 'men', NULL, NULL, 'categories/e6aDOEINe1Hhw27o20iAIER40gn114i4eu5GMiyH.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-11-27 06:58:42', '2025-12-02 01:20:01', NULL),
(8, 'Shirts', 'shirts', NULL, 7, 'categories/fgzaUES8M3F0iJeDJ4j7uVx8U1YeLiL8ywMMS3a2.jpg', 1, 0, 1, NULL, NULL, NULL, '2025-11-27 06:59:22', '2025-12-02 02:00:28', NULL),
(9, 'casual wear', 'casual-wear', NULL, 8, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-11-27 06:59:45', '2025-12-02 00:55:32', '2025-12-02 00:55:32'),
(10, 'Denim Shirts', 'denim-shirts', NULL, 19, 'categories/whJ7MUrEPAjZJTQsqS8EBYT8dFvZ9t44Kiq4Vxoe.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-11-27 07:00:15', '2025-12-02 01:51:56', NULL),
(15, 'Winter Wear', 'winter-wear', NULL, 7, 'categories/mGBXUSdbYnpFLjj1c4RHOcay2LaXluCIbgocwtyO.jpg', 1, 0, 2, NULL, NULL, NULL, '2025-12-02 01:25:47', '2025-12-02 02:00:55', NULL),
(16, 'Zipper', 'zipper', NULL, 15, 'categories/GW3U4SngOnHvq1lCfsbLVlgA4ny0XgcvOZn1jr2N.png', 1, 0, 1, NULL, NULL, NULL, '2025-12-02 01:30:10', '2025-12-02 03:21:51', NULL),
(17, 'Jeans', 'jeans', NULL, 7, 'categories/c1faODVu5T0ZxL7uwfjF5krZFGgrX700bQF9m9wj.webp', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:37:46', '2025-12-02 01:37:46', NULL),
(18, 'Regular Fit Jeans', 'regular-fit-jeans', NULL, 17, NULL, 1, 0, 1, NULL, NULL, NULL, '2025-12-02 01:42:36', '2025-12-02 01:42:36', NULL),
(19, 'Casual Wear Shirts', 'casual-wear-shirts', NULL, 8, 'categories/siMkkG9Fn5lwXCXqRkpKE6m5bM6ESFHfjFUCZ1vc.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:51:30', '2025-12-02 01:51:30', NULL),
(20, 'Women', 'women', NULL, NULL, 'categories/7kNs9I4pT5ZMMdIG3jD2E2XGXDNln5rbrYuJ69PW.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:53:35', '2025-12-02 02:02:04', NULL),
(21, 'Winter Wear', 'winter-wear-1', NULL, 20, 'categories/yxGLredQ96Pg0oj84fX00MLA7Io81Yq0ov1lZ0Gi.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:54:04', '2025-12-02 01:58:52', NULL),
(22, 'Poncho and Shawls', 'poncho-and-shawls', NULL, 21, 'categories/E4hdYbjfovspKmYjCI6Ss13AQMZkX0KGC1G0WCTz.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:54:18', '2025-12-02 01:59:21', NULL),
(23, 'Ethnic Wear', 'ethnic-wear', NULL, 20, 'categories/4wC6Ho7iPsHvIxbnlxIcvRxBydPgoa0hOpm4qlvG.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:54:55', '2025-12-02 01:57:50', NULL),
(24, 'Sarees', 'sarees', NULL, 23, 'categories/PtQlBhO0TjIuPKCV1XfkdgwinStsuID5vlLzKwUE.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:55:07', '2025-12-02 02:01:30', NULL),
(25, 'Designer Sarees', 'designer-sarees', NULL, 24, 'categories/Tsv5hO0RPhHjapDH9OglQusd1R4nIGtod34zKcH9.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:55:20', '2025-12-02 01:56:06', NULL),
(26, 'Printed Sarees', 'printed-sarees', NULL, 24, 'categories/YuqJJU2YvhOjpSNNt6iOHpcYx6NLzuykKX90wAAI.jpg', 1, 0, 0, NULL, NULL, NULL, '2025-12-02 01:55:38', '2025-12-02 01:56:36', NULL),
(27, 'gghhgg', 'gghhgg', NULL, 17, NULL, 1, 0, 0, NULL, NULL, NULL, '2025-12-03 05:46:30', '2025-12-03 06:54:26', '2025-12-03 06:54:26'),
(28, 'assential denim', 'assential-denim', NULL, 10, NULL, 1, 0, 1, NULL, NULL, NULL, '2025-12-03 06:50:35', '2025-12-09 23:17:13', '2025-12-09 23:17:13');

-- --------------------------------------------------------

--
-- Table structure for table `category_attributes`
--

CREATE TABLE `category_attributes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('text','number','select','multiselect','boolean','date','file') NOT NULL DEFAULT 'text',
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_filterable` tinyint(1) NOT NULL DEFAULT 0,
  `is_searchable` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category_product_attribute`
--

CREATE TABLE `category_product_attribute` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `product_attribute_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_product_attribute`
--

INSERT INTO `category_product_attribute` (`id`, `category_id`, `product_attribute_id`, `created_at`, `updated_at`) VALUES
(9, 27, 6, '2025-12-03 05:46:30', '2025-12-03 05:46:30');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `uses` int(11) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_uses`, `uses`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'SFDSFSDF', 'percentage', 10.00, 100.00, 12, 0, '2025-11-21', '2025-11-22', 1, '2025-11-21 02:08:19', '2025-11-21 02:08:19', NULL),
(2, '101010', 'fixed', 10.00, 100.00, 12, 0, '2025-11-21', '2025-11-23', 1, '2025-11-21 02:12:08', '2025-11-21 03:38:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `alternate_phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `preferred_contact_method` varchar(255) DEFAULT NULL,
  `preferred_payment_method` varchar(255) DEFAULT NULL,
  `preferred_delivery_slot` varchar(255) DEFAULT NULL,
  `newsletter_opt_in` tinyint(1) NOT NULL DEFAULT 0,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `risk_flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`risk_flags`)),
  `notes` text DEFAULT NULL,
  `custom_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_data`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `full_name`, `phone`, `alternate_phone`, `email`, `date_of_birth`, `gender`, `password`, `profile_image`, `preferred_contact_method`, `preferred_payment_method`, `preferred_delivery_slot`, `newsletter_opt_in`, `tags`, `risk_flags`, `notes`, `custom_data`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'gaurav', '887420110', '8872420110', 'admin@gmail.com', '2025-11-19', 'male', '$2y$12$QhgUGfRnBHZSM0Dhn.8hu.z8VBVGq5P2AFGSJ5rZ03IDidfQIiWzy', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '{\"password_confirmation\":\"123456789\",\"address_type\":\"home\",\"full_address\":\"test\",\"landmark\":null,\"state\":\"harayana\",\"city\":\"karnal\",\"pincode\":\"134109\",\"delivery_instructions\":null,\"customer_type\":null,\"special_instructions\":null}', 1, '2025-11-19 07:28:38', '2025-11-19 07:28:38', NULL),
(3, '12345', NULL, NULL, 'admin1@gmail.com', NULL, NULL, '$2y$12$nHuQagrjSmyHEFs5PRQZE.pnI4JcGrd3qagts2JAouaSAFq/NoPcq', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '{\"password_confirmation\":\"admin@1234\",\"address_type\":null,\"full_address\":\"full address is here\",\"landmark\":null,\"state\":\"haryana\",\"city\":\"karnal\",\"pincode\":\"134041\",\"delivery_instructions\":null,\"customer_type\":null,\"special_instructions\":null}', 1, '2025-11-20 00:27:25', '2025-11-20 00:27:25', NULL),
(4, 'test', NULL, NULL, 'admin2@gmail.com', NULL, NULL, '$2y$12$n99VSBMtlSetj7aua5O6s.wB1CJ4mM9hhuvfOXeMtwLcDH4vJeuES', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '{\"password_confirmation\":\"admin@1234\",\"address_type\":null,\"full_address\":\"test full address\",\"landmark\":null,\"state\":\"haryana\",\"city\":\"karnal ambala\",\"pincode\":\"123532\",\"delivery_instructions\":null,\"customer_type\":null,\"special_instructions\":null}', 1, '2025-11-20 01:23:09', '2025-11-20 01:23:09', NULL),
(5, 'test', NULL, NULL, 'admin4@gmail.com', NULL, NULL, '$2y$12$vuHqwUxu67LQ4PMED4LNa.MxY29pq85.4SpfW1vFgxYSPdctf5hq2', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '{\"password_confirmation\":\"12345678\",\"address_type\":null,\"full_address\":\"tet\",\"landmark\":null,\"state\":\"tst\",\"city\":\"tes\",\"pincode\":\"tset\",\"delivery_instructions\":null,\"customer_type\":null,\"special_instructions\":null}', 1, '2025-11-20 01:25:47', '2025-11-20 01:25:47', NULL),
(6, 'test', NULL, NULL, 'test@gmail.com', NULL, NULL, '$2y$12$zlZzS7e.qbS3wqzjOdM5r.4piX2hnFRtALj4cTwx9.p3RbfzhN9Ea', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, '{\"password_confirmation\":\"12345678\",\"address_type\":null,\"full_address\":\"of of\",\"landmark\":null,\"state\":\"of\",\"city\":\"of\",\"pincode\":\"of\",\"delivery_instructions\":null,\"customer_type\":null,\"special_instructions\":null}', 1, '2025-11-20 01:26:25', '2025-11-20 01:26:25', NULL),
(7, 'John Doe', '+91-9876543210', NULL, 'john@example.com', NULL, NULL, '$2y$12$WKwfSR4sm/gI.vEsIs2XPupbbmSEF8Q7PyPn/Y3RRx483s6OHW9Ge', NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, 1, '2025-11-24 23:23:46', '2025-11-24 23:23:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `address_type` enum('home','office','other') NOT NULL DEFAULT 'home',
  `full_address` text NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `state` varchar(255) NOT NULL,
  `city` varchar(255) NOT NULL,
  `pincode` varchar(255) NOT NULL,
  `delivery_instructions` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_addresses`
--

INSERT INTO `customer_addresses` (`id`, `customer_id`, `address_type`, `full_address`, `landmark`, `state`, `city`, `pincode`, `delivery_instructions`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 1, 'home', 'test', NULL, 'harayana', 'karnal', '134109', NULL, 0, '2025-11-19 07:28:38', '2025-11-19 07:28:38'),
(2, 3, 'home', 'full address is here', NULL, 'haryana', 'karnal', '134041', NULL, 0, '2025-11-20 00:27:25', '2025-11-20 00:27:25'),
(3, 4, 'home', 'test full address', NULL, 'haryana', 'karnal ambala', '123532', NULL, 0, '2025-11-20 01:23:09', '2025-11-20 01:23:09'),
(4, 5, 'home', 'tet', NULL, 'tst', 'tes', 'tset', NULL, 0, '2025-11-20 01:25:47', '2025-11-20 01:25:47'),
(5, 6, 'home', 'of of', NULL, 'of', 'of', 'of', NULL, 0, '2025-11-20 01:26:25', '2025-11-20 01:26:25');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `field_management_fields`
--

CREATE TABLE `field_management_fields` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `field_key` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `input_type` varchar(255) NOT NULL,
  `placeholder` varchar(255) DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `field_group` varchar(255) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `conditional_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditional_rules`)),
  `validation_rules` varchar(255) DEFAULT NULL,
  `help_text` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `field_management_fields`
--

INSERT INTO `field_management_fields` (`id`, `field_key`, `label`, `input_type`, `placeholder`, `is_required`, `is_visible`, `sort_order`, `field_group`, `options`, `conditional_rules`, `validation_rules`, `help_text`, `is_active`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'full_name', 'Full Name', 'text', 'Enter full name', 1, 1, 10, 'basic_info', NULL, NULL, 'required|max:255', NULL, 1, 1, '2025-11-19 06:02:20', '2025-11-24 23:31:55'),
(2, 'phone', 'Phone Number', 'tel', 'Enter phone number', 1, 1, 20, 'basic_info', NULL, NULL, 'max:20', NULL, 1, 1, '2025-11-19 06:02:20', '2025-11-24 23:31:55'),
(3, 'alternate_phone', 'Alternate Phone', 'tel', 'Enter alternate phone number', 0, 1, 30, 'basic_info', NULL, NULL, 'max:20', NULL, 1, 0, '2025-11-19 06:02:20', '2025-11-19 06:47:19'),
(4, 'email', 'Email Address', 'email', 'Enter email address', 1, 1, 40, 'basic_info', NULL, NULL, 'max:255', NULL, 1, 1, '2025-11-19 06:02:20', '2025-11-24 23:31:55'),
(5, 'date_of_birth', 'Date of Birth', 'date', NULL, 0, 1, 50, 'basic_info', NULL, NULL, NULL, NULL, 1, 0, '2025-11-19 06:02:20', '2025-11-24 23:23:06'),
(7, 'gender', 'Gender', 'select', NULL, 0, 1, 60, 'basic_info', '[{\"value\":\"male\",\"label\":\"Male\"},{\"value\":\"female\",\"label\":\"Female\"},{\"value\":\"other\",\"label\":\"Other\"}]', NULL, NULL, NULL, 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:47:19'),
(8, 'password', 'Password', 'password', 'Enter password', 0, 1, 10, 'credentials', NULL, NULL, 'min:8', 'Minimum 8 characters', 1, 1, '2025-11-19 06:14:12', '2025-11-24 23:31:55'),
(9, 'password_confirmation', 'Confirm Password', 'password', 'Confirm password', 0, 1, 20, 'credentials', NULL, NULL, 'min:8', NULL, 1, 1, '2025-11-19 06:14:12', '2025-11-24 23:31:55'),
(10, 'address_type', 'Address Type', 'select', NULL, 1, 1, 20, 'address', '[{\"value\":\"home\",\"label\":\"Home\"},{\"value\":\"office\",\"label\":\"Office\"},{\"value\":\"other\",\"label\":\"Other\"}]', NULL, NULL, NULL, 1, 1, '2025-11-19 06:14:12', '2025-11-24 23:31:55'),
(11, 'full_address', 'Full Address', 'textarea', 'Enter complete address', 1, 1, 21, 'address', NULL, NULL, NULL, NULL, 1, 1, '2025-11-19 06:14:12', '2025-11-24 23:31:55'),
(12, 'landmark', 'Landmark', 'text', 'Enter landmark', 0, 1, 22, 'address', NULL, NULL, 'max:255', NULL, 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:14:12'),
(13, 'state', 'State', 'text', 'Enter state', 1, 1, 23, 'address', NULL, NULL, 'max:255', NULL, 1, 1, '2025-11-19 06:14:12', '2025-11-24 23:31:55'),
(14, 'city', 'City', 'text', 'Enter city', 1, 1, 24, 'address', NULL, NULL, 'max:255', NULL, 1, 1, '2025-11-19 06:14:12', '2025-11-24 23:31:55'),
(15, 'pincode', 'Pincode', 'text', 'Enter pincode', 1, 1, 25, 'address', NULL, NULL, 'max:10', NULL, 1, 1, '2025-11-19 06:14:12', '2025-11-24 23:31:55'),
(16, 'delivery_instructions', 'Delivery Instructions', 'textarea', 'Enter delivery instructions (optional)', 0, 1, 26, 'address', NULL, NULL, NULL, NULL, 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:14:12'),
(17, 'make_default_address', 'Make Default Address', 'checkbox', NULL, 0, 1, 27, 'address', NULL, NULL, NULL, NULL, 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:14:12'),
(18, 'business_type', 'Business Type', 'select', NULL, 0, 1, 30, 'business', '[{\"value\":\"sole_proprietorship\",\"label\":\"Sole Proprietorship\"},{\"value\":\"partnership\",\"label\":\"Partnership\"},{\"value\":\"llc\",\"label\":\"LLC\"},{\"value\":\"corporation\",\"label\":\"Corporation\"}]', '{\"depends_on\":\"address_type\",\"show_when\":\"office\"}', NULL, NULL, 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:29:24'),
(19, 'company_name', 'Company Name', 'text', 'Enter company name', 0, 1, 31, 'business', NULL, '{\"depends_on\":\"address_type\",\"show_when\":\"office\"}', 'max:255', NULL, 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:29:25'),
(20, 'gstin', 'GSTIN / Tax ID', 'text', 'Enter GSTIN or Tax ID', 0, 1, 32, 'business', NULL, '{\"depends_on\":\"address_type\",\"show_when\":\"office\"}', 'max:50', NULL, 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:29:28'),
(21, 'preferred_contact_method', 'Preferred Contact Method', 'select', NULL, 0, 1, 40, 'preferences', '[{\"value\":\"call\",\"label\":\"Call\"},{\"value\":\"sms\",\"label\":\"SMS\"},{\"value\":\"whatsapp\",\"label\":\"WhatsApp\"},{\"value\":\"email\",\"label\":\"Email\"}]', NULL, NULL, NULL, 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:14:12'),
(22, 'preferred_payment_method', 'Preferred Payment Method', 'select', NULL, 0, 1, 41, 'preferences', '[{\"value\":\"cash\",\"label\":\"Cash\"},{\"value\":\"card\",\"label\":\"Card\"},{\"value\":\"upi\",\"label\":\"UPI\"},{\"value\":\"netbanking\",\"label\":\"Net Banking\"}]', NULL, NULL, NULL, 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:14:12'),
(23, 'preferred_delivery_slot', 'Preferred Delivery Slot', 'select', NULL, 0, 1, 42, 'preferences', '[{\"value\":\"morning\",\"label\":\"Morning (9 AM - 12 PM)\"},{\"value\":\"afternoon\",\"label\":\"Afternoon (12 PM - 5 PM)\"},{\"value\":\"evening\",\"label\":\"Evening (5 PM - 9 PM)\"}]', NULL, NULL, NULL, 0, 0, '2025-11-19 06:14:12', '2025-11-24 23:22:49'),
(24, 'profile_image', 'Profile Image', 'file', NULL, 0, 1, 50, 'qol', NULL, NULL, 'image|mimes:jpeg,png,jpg,gif|max:2048', 'Recommended size: 400x400px (Max: 2MB)', 1, 0, '2025-11-19 06:14:12', '2025-11-24 23:23:09'),
(25, 'newsletter_opt_in', 'Newsletter Opt-in', 'checkbox', NULL, 0, 1, 51, 'qol', NULL, NULL, NULL, 'Subscribe to our newsletter for updates and offers', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:29:43'),
(26, 'tags', 'Tags', 'text', 'Enter tags (comma separated)', 0, 0, 60, 'internal', NULL, NULL, NULL, 'Internal tags for categorization', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:29:44'),
(27, 'risk_flags', 'Risk Flags', 'text', 'Enter risk flags (comma separated)', 0, 0, 61, 'internal', NULL, NULL, NULL, 'Internal risk assessment flags', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:03'),
(28, 'notes', 'Notes', 'textarea', 'Enter internal notes', 0, 0, 62, 'internal', NULL, NULL, NULL, 'Internal notes about the customer', 0, 0, '2025-11-19 06:14:12', '2025-11-24 23:22:44'),
(29, 'referral_code', 'Referral Code', 'text', 'Enter referral code', 0, 1, 70, 'other', NULL, NULL, 'max:50', 'Referral code if customer was referred by someone', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:06'),
(30, 'customer_type', 'Customer Type', 'select', NULL, 0, 1, 71, 'other', '[{\"value\":\"retail\",\"label\":\"Retail\"},{\"value\":\"wholesale\",\"label\":\"Wholesale\"},{\"value\":\"corporate\",\"label\":\"Corporate\"},{\"value\":\"reseller\",\"label\":\"Reseller\"}]', NULL, NULL, 'Type of customer relationship', 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:14:12'),
(31, 'credit_limit', 'Credit Limit', 'number', 'Enter credit limit', 0, 1, 72, 'other', NULL, NULL, 'numeric|min:0', 'Credit limit in rupees', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:09'),
(32, 'payment_terms', 'Payment Terms', 'select', NULL, 0, 1, 73, 'other', '[{\"value\":\"cod\",\"label\":\"Cash on Delivery\"},{\"value\":\"net_15\",\"label\":\"Net 15\"},{\"value\":\"net_30\",\"label\":\"Net 30\"},{\"value\":\"net_45\",\"label\":\"Net 45\"},{\"value\":\"net_60\",\"label\":\"Net 60\"},{\"value\":\"prepaid\",\"label\":\"Prepaid\"}]', NULL, NULL, 'Payment terms for the customer', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:10'),
(33, 'tax_exempt', 'Tax Exempt', 'checkbox', NULL, 0, 1, 74, 'other', NULL, NULL, NULL, 'Check if customer is tax exempt', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:11'),
(34, 'discount_percentage', 'Discount Percentage', 'number', 'Enter discount percentage', 0, 1, 75, 'other', NULL, NULL, 'numeric|min:0|max:100', 'Default discount percentage for this customer (0-100)', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:12'),
(35, 'loyalty_points', 'Loyalty Points', 'number', 'Enter loyalty points', 0, 1, 76, 'other', NULL, NULL, 'numeric|min:0', 'Current loyalty points balance', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:14'),
(36, 'customer_since', 'Customer Since', 'date', NULL, 0, 1, 77, 'other', NULL, NULL, NULL, 'Date when customer first registered', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:15'),
(37, 'last_order_date', 'Last Order Date', 'date', NULL, 0, 1, 78, 'other', NULL, NULL, NULL, 'Date of last order placed', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:19'),
(38, 'total_orders', 'Total Orders', 'number', 'Enter total orders', 0, 1, 79, 'other', NULL, NULL, 'numeric|min:0', 'Total number of orders placed', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:23'),
(39, 'total_spent', 'Total Spent', 'number', 'Enter total amount spent', 0, 1, 80, 'other', NULL, NULL, 'numeric|min:0', 'Total amount spent in rupees', 0, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:20'),
(40, 'special_instructions', 'Special Instructions', 'textarea', 'Enter any special instructions', 0, 1, 81, 'other', NULL, NULL, NULL, 'Any special instructions or notes for this customer', 1, 0, '2025-11-19 06:14:12', '2025-11-19 06:30:25');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_stocks`
--

CREATE TABLE `inventory_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reserved_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Quantity reserved for pending orders',
  `available_quantity` int(11) GENERATED ALWAYS AS (`quantity` - `reserved_quantity`) VIRTUAL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_stocks`
--

INSERT INTO `inventory_stocks` (`id`, `product_variant_id`, `warehouse_id`, `warehouse_location_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES
(1, 86, 1, 1, 460, 0, '2025-12-12 04:21:17', '2025-12-12 07:24:02'),
(2, 84, 1, 2, 213, 0, '2025-12-12 07:24:25', '2025-12-12 07:24:25'),
(3, 84, 2, NULL, 25, 0, '2025-12-12 07:24:43', '2025-12-12 07:24:43');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `value` decimal(15,2) DEFAULT NULL COMMENT 'Expected deal amount',
  `status_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `priority` varchar(255) DEFAULT NULL COMMENT 'low, medium, high',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `name`, `email`, `phone`, `company`, `value`, `status_id`, `source_id`, `priority`, `assigned_to`, `description`, `created_at`, `updated_at`) VALUES
(1, 'test', 'test@gmail.com', 'test phone', 'test company', 120.00, 4, 3, 'high', 1, 'test  Description/Notes', '2025-11-18 04:37:16', '2025-11-19 01:41:33');

-- --------------------------------------------------------

--
-- Table structure for table `lead_activities`
--

CREATE TABLE `lead_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('note','call','email','meeting','file','reminder') NOT NULL,
  `description` text NOT NULL,
  `follow_up_date` timestamp NULL DEFAULT NULL,
  `next_action_owner` bigint(20) UNSIGNED DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL COMMENT 'For file upload type',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_activities`
--

INSERT INTO `lead_activities` (`id`, `lead_id`, `type`, `description`, `follow_up_date`, `next_action_owner`, `file_path`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'reminder', 'test', '2025-11-18 04:40:00', 1, NULL, 1, '2025-11-18 04:38:34', '2025-11-18 04:38:34'),
(2, 1, 'email', 'test', '2025-11-19 04:40:00', 1, NULL, 1, '2025-11-18 04:39:34', '2025-11-18 04:39:34'),
(3, 1, 'file', 'test', '2025-11-20 07:12:00', 1, 'lead-activities/AC9hYlUSKGw3JlrTSwvEDB3mKdZ8tC1INDg3ZzRj.png', 1, '2025-11-19 01:42:53', '2025-11-19 01:42:53');

-- --------------------------------------------------------

--
-- Table structure for table `lead_priorities`
--

CREATE TABLE `lead_priorities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL COMMENT 'Bootstrap color class (e.g., bg-success, bg-warning, bg-danger)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_priorities`
--

INSERT INTO `lead_priorities` (`id`, `name`, `slug`, `description`, `color`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Low', 'low', 'Low priority lead', 'bg-success', 1, 1, '2025-11-18 03:23:35', '2025-11-18 03:23:35'),
(2, 'Medium', 'medium', 'Medium priority lead', 'bg-warning', 1, 2, '2025-11-18 03:23:35', '2025-11-18 03:23:35'),
(3, 'High', 'high', 'High priority lead', 'bg-danger', 1, 3, '2025-11-18 03:23:35', '2025-11-18 03:23:35');

-- --------------------------------------------------------

--
-- Table structure for table `lead_reminders`
--

CREATE TABLE `lead_reminders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `reminder_note` text NOT NULL,
  `reminder_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_sources`
--

CREATE TABLE `lead_sources` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_sources`
--

INSERT INTO `lead_sources` (`id`, `name`, `slug`, `description`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Website', 'website', 'Lead came from website', 1, 1, '2025-11-18 03:23:32', '2025-11-19 00:01:53'),
(2, 'Referral', 'referral', 'Lead came from referral', 1, 2, '2025-11-18 03:23:32', '2025-11-18 03:23:32'),
(3, 'Ads', 'ads', 'Lead came from advertising campaigns', 1, 3, '2025-11-18 03:23:32', '2025-11-18 03:23:32'),
(4, 'Social', 'social', 'Lead came from social media', 1, 4, '2025-11-18 03:23:32', '2025-11-18 03:23:32'),
(5, 'Offline', 'offline', 'Lead came from offline channels', 1, 5, '2025-11-18 03:23:32', '2025-11-18 03:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `lead_statuses`
--

CREATE TABLE `lead_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL COMMENT 'Bootstrap color class (e.g., bg-info, bg-success)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_statuses`
--

INSERT INTO `lead_statuses` (`id`, `name`, `slug`, `description`, `color`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(2, 'In Progress', 'in_progress', 'Lead is being actively worked on', 'bg-primary', 1, 2, '2025-11-18 03:23:31', '2025-11-20 00:32:16'),
(3, 'Qualified', 'qualified', 'Lead has been qualified and is ready for conversion', 'bg-success', 1, 3, '2025-11-18 03:23:31', '2025-11-18 03:23:31'),
(4, 'Lost', 'lost', 'Lead opportunity was lost', 'bg-danger', 1, 4, '2025-11-18 03:23:31', '2025-11-18 03:23:31'),
(5, 'Won', 'won', 'Lead was successfully converted', 'bg-success', 1, 5, '2025-11-18 03:23:31', '2025-11-18 03:23:31');

-- --------------------------------------------------------

--
-- Table structure for table `lead_tag`
--

CREATE TABLE `lead_tag` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `lead_tag_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_tag`
--

INSERT INTO `lead_tag` (`id`, `lead_id`, `lead_tag_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-11-19 00:39:53', '2025-11-19 00:39:53'),
(2, 1, 2, '2025-11-19 00:39:53', '2025-11-19 00:39:53');

-- --------------------------------------------------------

--
-- Table structure for table `lead_tags`
--

CREATE TABLE `lead_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `color` varchar(255) DEFAULT NULL COMMENT 'Bootstrap color class',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_tags`
--

INSERT INTO `lead_tags` (`id`, `name`, `slug`, `color`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'testdd', 'testdd', 'teststst', 1, '2025-11-19 00:39:01', '2025-11-19 00:45:32'),
(2, 'testst', 'testst', 'bg-primary', 1, '2025-11-19 00:39:33', '2025-11-19 00:39:33');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_10_13_075734_create_categories_table', 1),
(5, '2025_10_13_092538_add_image_to_users_table', 1),
(6, '2025_10_13_095620_create_roles_table', 1),
(7, '2025_10_13_095632_create_permissions_table', 1),
(8, '2025_10_13_095644_create_role_user_table', 1),
(9, '2025_10_13_095659_create_permission_role_table', 1),
(10, '2025_10_13_103913_create_sections_table', 1),
(11, '2025_10_13_105023_create_pages_table', 1),
(12, '2025_10_13_105051_update_sections_table_add_page_id', 1),
(13, '2025_10_13_110920_add_sort_order_to_pages_table', 1),
(14, '2025_10_13_110935_rename_title_to_section_id_in_sections_table', 1),
(16, '2025_10_13_113323_create_product_images_table', 1),
(17, '2025_10_13_113332_create_product_variants_table', 1),
(18, '2025_10_13_113344_create_product_attributes_table', 1),
(19, '2025_10_13_113353_create_product_attribute_values_table', 1),
(20, '2025_10_13_113419_create_product_categories_table', 1),
(21, '2025_10_13_113849_add_missing_fields_to_categories_table', 1),
(22, '2025_10_13_124855_create_category_attributes_table', 1),
(23, '2025_10_13_125024_create_product_category_attribute_values_table', 1),
(24, '2025_10_14_092047_update_product_attributes_type_enum', 1),
(25, '2025_10_14_101729_add_cost_price_and_image_to_product_variants_table', 1),
(26, '2025_10_14_122347_create_brands_table', 1),
(27, '2025_10_14_122407_add_brand_id_to_categories_table', 1),
(28, '2025_10_14_125554_add_brand_id_to_products_table', 1),
(29, '2025_10_15_122336_create_product_brands_table', 1),
(30, '2025_10_15_122353_migrate_existing_brand_data_to_product_brands', 1),
(31, '2025_10_16_070919_create_units_table', 1),
(32, '2025_10_16_070933_add_unit_fields_to_products_table', 1),
(33, '2025_11_11_091017_alter_products_price_nullable', 2),
(34, '2025_11_11_102046_add_discount_fields_to_product_variants', 3),
(35, '2025_11_11_102113_add_product_variant_id_to_product_images', 4),
(36, '2025_11_12_000000_add_tags_to_products_table', 5),
(37, '2025_11_14_094400_migrate_brand_id_to_pivot_table', 6),
(38, '2025_11_14_094347_create_brand_category_table', 7),
(39, '2025_11_14_094352_remove_brand_id_from_categories_table', 8),
(40, '2025_11_18_075313_create_lead_statuses_table', 9),
(41, '2025_11_18_075314_create_lead_sources_table', 10),
(42, '2025_11_18_075317_create_lead_priorities_table', 11),
(43, '2025_11_18_094017_create_leads_table', 12),
(44, '2025_11_18_094021_create_lead_tags_table', 13),
(45, '2025_11_18_094022_create_lead_tag_pivot_table', 14),
(46, '2025_11_18_094018_create_lead_activities_table', 15),
(47, '2025_11_18_094019_create_lead_reminders_table', 16),
(48, '2025_11_19_110205_create_field_management_fields_table', 17),
(49, '2025_11_19_110208_create_customers_table', 18),
(50, '2025_11_19_110213_create_customer_addresses_table', 19),
(51, '2025_11_20_070859_create_orders_table', 20),
(52, '2025_11_20_070903_create_order_items_table', 21),
(53, '2025_01_15_000001_enhance_permissions_table', 22),
(54, '2025_01_15_000002_enhance_roles_table', 22),
(55, '2025_01_15_000003_add_user_permissions_table', 22),
(56, '2025_11_21_071146_create_coupons_table', 23),
(57, '2025_11_21_092531_create_carts_table', 24),
(58, '2025_11_21_092535_create_cart_items_table', 25),
(59, '2025_11_14_050207_create_personal_access_tokens_table', 26),
(60, '2025_11_21_120212_add_is_system_to_field_management_fields_table', 27),
(61, '2025_11_26_080900_drop_brand_category_table', 28),
(62, '2025_11_26_085422_add_category_id_to_products_table', 29),
(63, '2025_11_26_085436_migrate_product_categories_to_single_category_id', 30),
(64, '2025_11_26_113915_create_product_attribute_values_table_for_static_attributes', 31),
(65, '2025_11_27_065531_add_sku_type_to_products_table', 32),
(66, '2025_12_02_064318_update_categories_slug_unique_for_soft_deletes', 33),
(67, '2025_12_03_125103_add_source_to_orders_table', 34),
(68, '2025_12_09_061646_remove_product_level_inventory_fields_from_products_table', 35),
(69, '2025_12_09_061658_add_low_stock_threshold_to_product_variants_table', 36),
(70, '2025_12_09_061659_add_metadata_json_ld_to_products_table', 37),
(71, '2025_12_09_065322_add_barcode_to_product_variants_table', 38),
(72, '2025_12_09_093132_add_highlights_details_to_product_variants_table', 39),
(73, '2025_12_09_093135_create_variant_heading_suggestions_table', 40),
(74, '2025_12_12_090414_create_warehouses_table', 41),
(75, '2025_12_12_090415_create_warehouse_locations_table', 42),
(76, '2025_12_12_091121_add_default_warehouse_id_to_products_table', 43),
(77, '2025_12_12_091159_create_inventory_stocks_table', 44),
(78, '2025_12_12_092927_add_warehouse_id_to_order_items_table', 45),
(79, '2025_12_12_101507_add_is_default_to_warehouses_table', 46),
(80, '2025_12_12_115527_create_shipping_zones_table', 47),
(81, '2025_12_12_115530_create_shipping_methods_table', 48),
(82, '2025_12_12_115531_create_shipping_rates_table', 49);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL DEFAULT 'admin',
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `source`, `customer_id`, `status`, `subtotal`, `tax_amount`, `shipping_amount`, `discount_amount`, `total_amount`, `payment_method`, `payment_status`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ORD-6925A6D458638', 'admin', 7, 'shipped', 3897.00, 0.00, 0.00, 0.00, 3897.00, 'cod', 'pending', '{\"shipping_address\":{\"address_line1\":\"123 Main St\",\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"zip_code\":\"400001\",\"country\":\"India\",\"phone\":\"+91-9876543210\"},\"billing_address\":{\"address_line1\":\"123 Main St\",\"city\":\"Mumbai\",\"state\":\"Maharashtra\",\"zip_code\":\"400001\",\"country\":\"India\",\"phone\":\"+91-9876543210\"},\"order_notes\":null}', '2025-11-25 07:23:40', '2025-12-03 23:33:37', NULL),
(2, 'ORD-69311BB458F52', 'admin', 7, 'pending', 2397.00, 0.00, 0.00, 0.00, 2397.00, NULL, 'pending', NULL, '2025-12-03 23:57:16', '2025-12-03 23:57:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warehouse_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(255) NOT NULL,
  `variant_name` varchar(255) DEFAULT NULL,
  `variant_sku` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_variant_id`, `warehouse_id`, `warehouse_location_id`, `product_name`, `product_sku`, `variant_name`, `variant_sku`, `quantity`, `unit_price`, `total_price`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, NULL, NULL, 'Apple iPhone 17 Pro', 'PROD-IPH17', 'iPhone 17 Pro 128GB - Midnight Black', 'IP17-128-MBK', 3, 1299.00, 3897.00, '2025-11-25 07:23:40', '2025-11-25 07:23:40'),
(2, 2, 14, 84, NULL, NULL, 'Grey Collared Zipper', 'PRD-GREY-COLLARED-ZIPPER-805516', 'Grey', 'GREY-1-GRE', 1, 799.00, 799.00, '2025-12-03 23:57:16', '2025-12-03 23:57:16'),
(3, 2, 14, 84, NULL, NULL, 'Grey Collared Zipper', 'PRD-GREY-COLLARED-ZIPPER-805516', 'Grey', 'GREY-1-GRE', 2, 799.00, 1598.00, '2025-12-03 23:57:16', '2025-12-03 23:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `name`, `slug`, `url`, `sort_order`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Home page', 'home-page', '/home', 0, NULL, 1, '2025-11-14 23:51:26', '2025-11-14 23:51:26');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `module` varchar(255) DEFAULT NULL,
  `resource` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `module`, `resource`, `action`, `group`, `sort_order`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(1, 'View Dashboard', 'view-dashboard', NULL, NULL, NULL, NULL, 0, 1, 'Can view the admin dashboard', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(2, 'View Categories', 'view-categories', NULL, NULL, NULL, NULL, 0, 1, 'Can view categories list', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(3, 'Create Categories', 'create-categories', NULL, NULL, NULL, NULL, 0, 1, 'Can create new categories', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(4, 'Edit Categories', 'edit-categories', NULL, NULL, NULL, NULL, 0, 1, 'Can edit existing categories', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(5, 'Delete Categories', 'delete-categories', NULL, NULL, NULL, NULL, 0, 1, 'Can delete categories', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(6, 'View Products', 'view-products', NULL, NULL, NULL, NULL, 0, 1, 'Can view products list', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(7, 'Create Products', 'create-products', NULL, NULL, NULL, NULL, 0, 1, 'Can create new products', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(8, 'Edit Products', 'edit-products', NULL, NULL, NULL, NULL, 0, 1, 'Can edit existing products', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(9, 'Delete Products', 'delete-products', NULL, NULL, NULL, NULL, 0, 1, 'Can delete products', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(10, 'View Orders', 'view-orders', NULL, NULL, NULL, NULL, 0, 1, 'Can view orders list', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(11, 'Edit Orders', 'edit-orders', NULL, NULL, NULL, NULL, 0, 1, 'Can edit order status', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(12, 'Delete Orders', 'delete-orders', NULL, NULL, NULL, NULL, 0, 1, 'Can delete orders', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(13, 'View Customers', 'view-customers', NULL, NULL, NULL, NULL, 0, 1, 'Can view customers list', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(14, 'Edit Customers', 'edit-customers', NULL, NULL, NULL, NULL, 0, 1, 'Can edit customer information', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(15, 'Delete Customers', 'delete-customers', NULL, NULL, NULL, NULL, 0, 1, 'Can delete customers', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(16, 'View Users', 'view-users', NULL, NULL, NULL, NULL, 0, 1, 'Can view users list', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(17, 'Create Users', 'create-users', NULL, NULL, NULL, NULL, 0, 1, 'Can create new users', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(18, 'Edit Users', 'edit-users', NULL, NULL, NULL, NULL, 0, 1, 'Can edit user information', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(19, 'Delete Users', 'delete-users', NULL, NULL, NULL, NULL, 0, 1, 'Can delete users', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(20, 'View Roles', 'view-roles', NULL, NULL, NULL, NULL, 0, 1, 'Can view roles list', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(21, 'Create Roles', 'create-roles', NULL, NULL, NULL, NULL, 0, 1, 'Can create new roles', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(22, 'Edit Roles', 'edit-roles', NULL, NULL, NULL, NULL, 0, 1, 'Can edit existing roles', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(23, 'Delete Roles', 'delete-roles', NULL, NULL, NULL, NULL, 0, 1, 'Can delete roles', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(24, 'Assign Roles', 'assign-roles', NULL, NULL, NULL, NULL, 0, 1, 'Can assign roles to users', '2025-10-16 06:17:39', '2025-11-21 00:04:10'),
(25, 'View Permissions', 'view-permissions', NULL, NULL, NULL, NULL, 0, 1, 'Can view permissions list', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(26, 'Create Permissions', 'create-permissions', NULL, NULL, NULL, NULL, 0, 1, 'Can create new permissions', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(27, 'Edit Permissions', 'edit-permissions', NULL, NULL, NULL, NULL, 0, 1, 'Can edit existing permissions', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(28, 'Delete Permissions', 'delete-permissions', NULL, NULL, NULL, NULL, 0, 1, 'Can delete permissions', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(29, 'View Settings', 'view-settings', NULL, NULL, NULL, NULL, 0, 1, 'Can view system settings', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(30, 'Edit Settings', 'edit-settings', NULL, NULL, NULL, NULL, 0, 1, 'Can edit system settings', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(31, 'View Reports', 'view-reports', NULL, NULL, NULL, NULL, 0, 1, 'Can view reports and analytics', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(32, 'Export Reports', 'export-reports', NULL, NULL, NULL, NULL, 0, 1, 'Can export reports', '2025-10-16 06:17:39', '2025-10-16 06:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permission_role`
--

INSERT INTO `permission_role` (`id`, `permission_id`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(2, 2, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(3, 3, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(4, 4, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(5, 5, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(6, 6, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(7, 7, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(8, 8, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(9, 9, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(10, 10, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(11, 11, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(12, 12, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(13, 13, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(14, 14, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(15, 15, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(16, 16, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(17, 17, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(18, 18, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(19, 19, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(20, 20, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(21, 21, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(22, 22, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(23, 23, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(24, 24, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(25, 25, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(26, 26, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(27, 27, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(28, 28, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(29, 29, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(30, 30, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(31, 31, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(32, 32, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(33, 3, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(34, 7, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(35, 4, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(36, 14, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(37, 11, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(38, 8, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(39, 32, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(40, 2, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(41, 13, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(42, 1, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(43, 10, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(44, 6, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(45, 31, 2, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(46, 3, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(47, 7, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(48, 4, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(49, 8, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(50, 2, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(51, 13, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(52, 1, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(53, 10, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(54, 6, 3, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(55, 2, 4, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(56, 13, 4, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(57, 1, 4, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(58, 10, 4, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(59, 6, 4, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(60, 31, 4, '2025-10-16 06:17:39', '2025-10-16 06:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `permission_user`
--

CREATE TABLE `permission_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `granted` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\Customer', 7, 'auth-token', '0b4b2822a233ef77ab538b0d3f59c3641064650169a08fad690a404181c49d9c', '[\"*\"]', NULL, NULL, '2025-11-24 23:23:46', '2025-11-24 23:23:46'),
(2, 'App\\Models\\Customer', 7, 'auth-token', '639ba4dfe52437431821c380e47eb9665ab6fa665e87b5e167f5b71311a0e35a', '[\"*\"]', '2025-11-25 07:23:40', NULL, '2025-11-24 23:34:19', '2025-11-25 07:23:40');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `default_warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit_quantity` decimal(10,3) DEFAULT NULL,
  `unit_display` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL,
  `material` varchar(255) DEFAULT NULL,
  `origin_country` varchar(255) DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_perishable` tinyint(1) NOT NULL DEFAULT 0,
  `requires_prescription` tinyint(1) NOT NULL DEFAULT 0,
  `is_hazardous` tinyint(1) NOT NULL DEFAULT 0,
  `ingredients` text DEFAULT NULL,
  `nutritional_info` text DEFAULT NULL,
  `barcode_type` varchar(255) NOT NULL DEFAULT 'EAN13',
  `custom_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_attributes`)),
  `requires_shipping` tinyint(1) NOT NULL DEFAULT 1,
  `free_shipping` tinyint(1) NOT NULL DEFAULT 0,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  `json_ld` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`json_ld`)),
  `tags` text DEFAULT NULL,
  `status` enum('draft','published','scheduled','hidden') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `download_limit` varchar(255) DEFAULT NULL,
  `download_expiry` varchar(255) DEFAULT NULL,
  `bundle_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bundle_items`)),
  `subscription_period` enum('day','week','month','year') DEFAULT NULL,
  `subscription_interval` int(11) DEFAULT NULL,
  `subscription_length` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `short_description`, `category_id`, `default_warehouse_id`, `brand_id`, `unit_id`, `unit_quantity`, `unit_display`, `color`, `size`, `material`, `origin_country`, `manufacturing_date`, `expiry_date`, `is_perishable`, `requires_prescription`, `is_hazardous`, `ingredients`, `nutritional_info`, `barcode_type`, `custom_attributes`, `requires_shipping`, `free_shipping`, `meta_title`, `meta_description`, `meta_keywords`, `metadata`, `json_ld`, `tags`, `status`, `published_at`, `featured`, `download_limit`, `download_expiry`, `bundle_items`, `subscription_period`, `subscription_interval`, `subscription_length`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Men\'s Cotton Polo T-Shirt', 'mens-cotton-polo-tshirt', 'Classic cotton polo t-shirt with comfortable fit and breathable fabric. Perfect for casual wear and everyday comfort.', 'Classic cotton polo t-shirt with comfortable fit', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:18:33', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(2, 'Smartphone', 'smartphone', 'Latest generation smartphone with advanced camera system, long battery life, and premium build quality.', 'Latest generation smartphone with advanced features', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:18:33', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(3, 'Running Shoes', 'running-shoes', 'Lightweight running shoes with excellent cushioning, breathable mesh upper, and superior traction for all terrains.', 'Lightweight running shoes with excellent cushioning', 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:18:33', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(4, 'Women\'s Summer Dress', 'womens-summer-dress', 'Lightweight summer dress with floral pattern and flowing silhouette. Perfect for warm weather and casual occasions.', 'Lightweight summer dress with floral pattern', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:18:34', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(5, 'Silver Vertical Bar Pendant for Men', 'silver-vertical-bar-pendant-men', 'Elegant sterling silver vertical bar pendant with modern minimalist design. Perfect for special occasions and everyday wear.', 'Elegant sterling silver vertical bar pendant', 4, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:18:34', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(6, 'Leather Wallet', 'leather-wallet', 'Premium genuine leather wallet with multiple card slots, cash compartment, and RFID blocking technology.', 'Premium genuine leather wallet', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:18:34', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(7, 'Stainless Steel Water Bottle', 'stainless-steel-water-bottle', 'BPA-free stainless steel water bottle with leak-proof lid, double-wall insulation, and ergonomic design. Keeps drinks cold for 24 hours.', 'BPA-free stainless steel water bottle', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:22:12', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(8, 'Wireless Earbuds', 'wireless-earbuds', 'Premium wireless earbuds with active noise cancellation, 24-hour battery life, and crystal-clear sound quality.', 'Premium wireless earbuds with noise cancellation', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:22:12', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(9, 'Men\'s Denim Jeans', 'mens-denim-jeans', 'Classic fit denim jeans with stretch comfort, durable construction, and timeless style. Perfect for casual and smart-casual wear.', 'Classic fit denim jeans with stretch comfort', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:22:12', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(10, 'Laptop', 'laptop', 'High-performance laptop with fast processor, vibrant display, and long battery life. Perfect for work and entertainment.', 'High-performance laptop with fast processor', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:22:12', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(11, 'Canvas Backpack', 'canvas-backpack', 'Durable canvas backpack with multiple compartments, padded laptop sleeve, and adjustable shoulder straps. Perfect for school, work, or travel.', 'Durable canvas backpack with multiple compartments', 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:22:13', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(12, 'Fitness Tracker', 'fitness-tracker', 'Advanced fitness tracker with heart rate monitor, sleep tracking, step counter, and smartphone notifications. Water-resistant design.', 'Advanced fitness tracker with heart rate monitor', 3, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 1, 0, NULL, NULL, NULL, NULL, NULL, NULL, 'published', NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, '2025-11-27 02:22:13', '2025-12-02 03:37:41', '2025-12-02 03:37:41'),
(14, 'Grey Collared Zipper', 'grey-collared-zipper', NULL, '<p>Elevate your everyday style with this premium Grey Knitted Cotton Full-Zip Jacket, meticulously crafted from 100% cotton for unparalleled comfort and breathability. Its sophisticated collar neck and full sleeves offer a refined silhouette, perfect for the modern man seeking versatile layering. Designed with a regular fit and a solid pattern, this zipper jacket provides enduring comfort and effortless style for any casual or smart-casual occasion. Seamlessly transition from day to evening with this essential piece, embodying a polished aesthetic that complements every ensemble.</p>', 10, 1, 22, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, NULL, NULL, 'EAN13', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, 'tag1, tag2', 'published', NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-02 06:57:49', '2025-12-12 05:17:03', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('select','color','image','text','number','date','boolean') DEFAULT 'select',
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `is_variation` tinyint(1) NOT NULL DEFAULT 0,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_attributes`
--

INSERT INTO `product_attributes` (`id`, `name`, `slug`, `description`, `type`, `is_required`, `is_variation`, `is_visible`, `sort_order`, `created_at`, `updated_at`) VALUES
(5, 'size', 'size', NULL, 'text', 1, 1, 1, 1, '2025-12-02 03:44:22', '2025-12-02 03:44:22'),
(6, 'color', 'color', NULL, 'color', 1, 1, 1, 2, '2025-12-02 03:44:38', '2025-12-02 03:44:38'),
(7, 'Chest Size', 'chest-size', NULL, 'number', 0, 0, 1, 3, '2025-12-02 06:42:41', '2025-12-02 06:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `product_attribute_values`
--

CREATE TABLE `product_attribute_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `attribute_id` bigint(20) UNSIGNED NOT NULL,
  `value` varchar(255) NOT NULL,
  `color_code` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_attribute_values`
--

INSERT INTO `product_attribute_values` (`id`, `attribute_id`, `value`, `color_code`, `image_path`, `sort_order`, `created_at`, `updated_at`) VALUES
(18, 5, 's', NULL, NULL, 1, '2025-12-02 03:44:50', '2025-12-02 03:44:50'),
(19, 5, 'M', NULL, NULL, 2, '2025-12-02 03:44:56', '2025-12-02 03:45:07'),
(20, 5, 'L', NULL, NULL, 3, '2025-12-02 03:45:08', '2025-12-02 03:45:08'),
(21, 5, 'XL', NULL, NULL, 4, '2025-12-02 03:45:23', '2025-12-02 03:45:23'),
(22, 5, 'XXL', NULL, NULL, 5, '2025-12-02 03:45:36', '2025-12-02 03:45:36'),
(23, 6, 'Grey', '#c0c0c0', NULL, 1, '2025-12-02 03:47:20', '2025-12-02 03:47:20'),
(24, 6, 'Navy', '#004080', NULL, 2, '2025-12-02 03:57:10', '2025-12-02 03:57:10'),
(25, 6, 'Olive', '#808040', NULL, 3, '2025-12-02 03:57:23', '2025-12-02 03:57:23'),
(26, 6, 'Aqua', '#03d0fc', NULL, 4, '2025-12-02 03:57:46', '2025-12-02 03:57:46'),
(27, 6, 'Orange', '#FF9800', NULL, 5, '2025-12-02 04:57:16', '2025-12-02 04:57:16');

-- --------------------------------------------------------

--
-- Table structure for table `product_brands`
--

CREATE TABLE `product_brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `brand_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_brands`
--

INSERT INTO `product_brands` (`id`, `product_id`, `brand_id`, `is_primary`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 0, '2025-11-10 06:59:41', '2025-11-19 03:52:16'),
(2, 2, 2, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(3, 3, 4, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(4, 4, 5, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(5, 5, 4, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(6, 6, 6, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(7, 7, 7, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(8, 8, 6, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(9, 9, 8, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(10, 10, 8, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(11, 11, 9, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(12, 12, 9, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(13, 13, 10, 1, 0, '2025-11-11 03:46:39', '2025-11-11 03:46:39'),
(14, 26, 10, 1, 0, '2025-11-11 04:31:25', '2025-11-11 04:31:25'),
(15, 27, 10, 1, 0, '2025-11-11 04:34:39', '2025-11-11 04:34:39'),
(16, 28, 10, 1, 0, '2025-11-11 04:37:50', '2025-11-14 23:06:45'),
(18, 28, 1, 0, 1, '2025-11-11 05:19:10', '2025-11-14 23:06:45'),
(19, 29, 10, 1, 0, '2025-11-12 23:25:51', '2025-11-14 05:20:30'),
(20, 29, 1, 0, 1, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(21, 29, 2, 0, 2, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(22, 29, 3, 0, 3, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(23, 29, 4, 0, 4, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(24, 30, 11, 1, 0, '2025-11-27 01:52:46', '2025-11-27 02:03:20'),
(25, 31, 12, 1, 0, '2025-11-27 01:54:12', '2025-11-27 02:03:21'),
(26, 32, 13, 1, 0, '2025-11-27 01:54:13', '2025-11-27 02:03:21'),
(27, 33, 14, 1, 0, '2025-11-27 01:54:13', '2025-11-27 02:03:21'),
(28, 34, 11, 1, 0, '2025-11-27 01:54:13', '2025-11-27 02:03:21'),
(29, 35, 12, 1, 0, '2025-11-27 01:54:13', '2025-11-27 02:03:21'),
(30, 36, 13, 1, 0, '2025-11-27 01:54:13', '2025-11-27 02:03:21'),
(31, 37, 11, 1, 0, '2025-11-27 01:54:13', '2025-11-27 02:03:21'),
(32, 1, 15, 1, 0, '2025-11-27 02:18:33', '2025-11-27 02:22:11'),
(33, 2, 16, 1, 0, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(34, 3, 17, 1, 0, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(35, 4, 15, 1, 0, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(36, 5, 18, 1, 0, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(37, 6, 15, 1, 0, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(38, 7, 17, 1, 0, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(39, 8, 16, 1, 0, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(40, 9, 15, 1, 0, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(41, 10, 16, 1, 0, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(42, 11, 15, 1, 0, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(43, 12, 17, 1, 0, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(44, 14, 22, 1, 0, '2025-12-02 06:57:49', '2025-12-12 05:17:03'),
(45, 14, 19, 0, 1, '2025-12-02 06:57:49', '2025-12-12 05:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `product_id`, `category_id`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-11-10 06:59:41', '2025-11-27 02:22:11'),
(3, 2, 1, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(4, 2, 3, 1, '2025-11-10 06:59:41', '2025-11-27 02:22:12'),
(5, 3, 4, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(6, 3, 5, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(7, 4, 4, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(8, 4, 6, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(9, 5, 4, 1, '2025-11-10 06:59:41', '2025-11-27 02:22:12'),
(10, 5, 7, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(11, 6, 8, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(12, 6, 9, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(13, 7, 8, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(14, 7, 9, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(15, 8, 8, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(16, 8, 11, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(17, 9, 12, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(18, 9, 13, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(19, 10, 12, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(20, 10, 14, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(21, 11, 15, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(22, 11, 16, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(23, 12, 15, 1, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(24, 12, 16, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(25, 28, 1, 1, '2025-11-11 05:12:48', '2025-11-14 23:06:45'),
(37, 28, 4, 0, '2025-11-11 07:11:20', '2025-11-14 23:06:45'),
(38, 28, 8, 0, '2025-11-11 07:11:20', '2025-11-14 23:06:45'),
(39, 28, 6, 0, '2025-11-11 07:11:20', '2025-11-14 23:06:45'),
(40, 28, 3, 0, '2025-11-11 07:11:20', '2025-11-14 23:06:45'),
(41, 29, 1, 1, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(42, 29, 8, 0, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(43, 29, 12, 0, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(44, 29, 16, 0, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(45, 29, 3, 0, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(46, 29, 14, 0, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(47, 29, 10, 0, '2025-11-14 05:19:30', '2025-11-14 05:20:30'),
(48, 3, 2, 1, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(49, 4, 1, 1, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(50, 6, 5, 1, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(51, 7, 1, 1, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(52, 8, 3, 1, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(53, 9, 1, 1, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(54, 10, 3, 1, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(55, 11, 5, 1, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(56, 12, 3, 1, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(57, 14, 16, 0, '2025-12-02 06:57:49', '2025-12-12 05:17:03'),
(60, 14, 10, 1, '2025-12-09 06:21:33', '2025-12-12 05:17:03');

-- --------------------------------------------------------

--
-- Table structure for table `product_category_attribute_values`
--

CREATE TABLE `product_category_attribute_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `category_attribute_id` bigint(20) UNSIGNED NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `product_variant_id`, `image_path`, `alt_text`, `sort_order`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 14, NULL, 'products/Fbee5O1TAEEsYTijLciFOevh6CHiPZOxDpMj4U9z.png', NULL, 0, 1, '2025-12-02 06:57:49', '2025-12-12 05:17:03'),
(3, 14, 84, 'products/variants/8wVuI8YXGzXNbawUJMpAcdp2CC64bYwEkWAfz2oX.png', NULL, 0, 1, '2025-12-02 06:57:49', '2025-12-02 06:57:49'),
(4, 14, 84, 'products/variants/phgAYarhFGW0Al0b2BPimytQTZE0QtPRVXTZJ8NB.png', NULL, 1, 0, '2025-12-02 06:57:49', '2025-12-02 06:57:49'),
(5, 14, 84, 'products/variants/QsbunO1T6OkHP3d1HHvIknqYzRmPBZS1noPUn13M.png', NULL, 2, 0, '2025-12-02 06:57:49', '2025-12-02 06:57:49'),
(6, 14, 84, 'products/variants/kl8MGIlm8pqOFIPWQuNPJezFaxMQenEP8BzOBd67.png', NULL, 3, 0, '2025-12-02 06:57:49', '2025-12-02 06:57:49'),
(7, 14, 86, 'products/variants/MZX21VGqmYEmPRz8w9cJg4b9hxAy1E6OxQFIJYJj.jpg', NULL, 0, 1, '2025-12-09 05:55:18', '2025-12-09 05:55:18');

-- --------------------------------------------------------

--
-- Table structure for table `product_static_attributes`
--

CREATE TABLE `product_static_attributes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `attribute_id` bigint(20) UNSIGNED NOT NULL,
  `value_id` bigint(20) UNSIGNED DEFAULT NULL,
  `custom_value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(255) NOT NULL,
  `barcode` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`attributes`)),
  `measurements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`measurements`)),
  `highlights_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`highlights_details`)),
  `price` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `discount_type` varchar(255) DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT NULL,
  `discount_active` tinyint(1) NOT NULL DEFAULT 0,
  `sale_price_start` timestamp NULL DEFAULT NULL,
  `sale_price_end` timestamp NULL DEFAULT NULL,
  `manage_stock` tinyint(1) NOT NULL DEFAULT 1,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 0,
  `stock_status` enum('in_stock','out_of_stock','on_backorder') NOT NULL DEFAULT 'in_stock',
  `weight` decimal(8,2) DEFAULT NULL,
  `length` decimal(8,2) DEFAULT NULL,
  `width` decimal(8,2) DEFAULT NULL,
  `height` decimal(8,2) DEFAULT NULL,
  `diameter` decimal(8,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `sku`, `barcode`, `name`, `attributes`, `measurements`, `highlights_details`, `price`, `sale_price`, `cost_price`, `image`, `discount_type`, `discount_value`, `discount_active`, `sale_price_start`, `sale_price_end`, `manage_stock`, `stock_quantity`, `low_stock_threshold`, `stock_status`, `weight`, `length`, `width`, `height`, `diameter`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'POLO-001-BLACK-L', NULL, 'Black - L', '{\"Color\":\"Black\",\"Clothing Size\":\"L\"}', NULL, NULL, 26.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:18:33', '2025-11-27 02:22:11'),
(2, 1, 'POLO-001-BLACK-M', NULL, 'Black - M', '{\"Color\":\"Black\",\"Clothing Size\":\"M\"}', NULL, NULL, 27.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-11-27 02:18:33', '2025-11-27 02:22:11'),
(3, 1, 'POLO-001-BLACK-S', NULL, 'Black - S', '{\"Color\":\"Black\",\"Clothing Size\":\"S\"}', NULL, NULL, 38.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 2, '2025-11-27 02:18:33', '2025-11-27 02:22:11'),
(4, 1, 'POLO-001-BLACK-XL', NULL, 'Black - XL', '{\"Color\":\"Black\",\"Clothing Size\":\"XL\"}', NULL, NULL, 25.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 3, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(5, 1, 'POLO-001-BLACK-XXL', NULL, 'Black - XXL', '{\"Color\":\"Black\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 32.99, 28.04, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 4, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(6, 1, 'POLO-001-NAVY-L', NULL, 'Navy - L', '{\"Color\":\"Navy\",\"Clothing Size\":\"L\"}', NULL, NULL, 38.99, 33.14, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 5, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(7, 1, 'POLO-001-NAVY-M', NULL, 'Navy - M', '{\"Color\":\"Navy\",\"Clothing Size\":\"M\"}', NULL, NULL, 37.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 6, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(8, 1, 'POLO-001-NAVY-S', NULL, 'Navy - S', '{\"Color\":\"Navy\",\"Clothing Size\":\"S\"}', NULL, NULL, 39.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 7, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(9, 1, 'POLO-001-NAVY-XL', NULL, 'Navy - XL', '{\"Color\":\"Navy\",\"Clothing Size\":\"XL\"}', NULL, NULL, 25.99, 22.09, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 8, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(10, 1, 'POLO-001-NAVY-XXL', NULL, 'Navy - XXL', '{\"Color\":\"Navy\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 33.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 9, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(11, 1, 'POLO-001-OLIVE-L', NULL, 'Olive - L', '{\"Color\":\"Olive\",\"Clothing Size\":\"L\"}', NULL, NULL, 35.99, 30.59, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 10, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(12, 1, 'POLO-001-OLIVE-M', NULL, 'Olive - M', '{\"Color\":\"Olive\",\"Clothing Size\":\"M\"}', NULL, NULL, 26.99, 22.94, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 11, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(13, 1, 'POLO-001-OLIVE-S', NULL, 'Olive - S', '{\"Color\":\"Olive\",\"Clothing Size\":\"S\"}', NULL, NULL, 36.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 12, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(14, 1, 'POLO-001-OLIVE-XL', NULL, 'Olive - XL', '{\"Color\":\"Olive\",\"Clothing Size\":\"XL\"}', NULL, NULL, 29.99, 25.49, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 13, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(15, 1, 'POLO-001-OLIVE-XXL', NULL, 'Olive - XXL', '{\"Color\":\"Olive\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 28.99, 24.64, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 14, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(16, 1, 'POLO-001-WHITE-L', NULL, 'White - L', '{\"Color\":\"White\",\"Clothing Size\":\"L\"}', NULL, NULL, 36.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 15, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(17, 1, 'POLO-001-WHITE-M', NULL, 'White - M', '{\"Color\":\"White\",\"Clothing Size\":\"M\"}', NULL, NULL, 26.99, 22.94, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 16, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(18, 1, 'POLO-001-WHITE-S', NULL, 'White - S', '{\"Color\":\"White\",\"Clothing Size\":\"S\"}', NULL, NULL, 26.99, 22.94, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 17, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(19, 1, 'POLO-001-WHITE-XL', NULL, 'White - XL', '{\"Color\":\"White\",\"Clothing Size\":\"XL\"}', NULL, NULL, 36.99, 31.44, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 18, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(20, 1, 'POLO-001-WHITE-XXL', NULL, 'White - XXL', '{\"Color\":\"White\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 27.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 19, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(21, 2, 'PHONE-001-128GB-BLACK', NULL, '128GB - Black', '{\"Storage Capacity\":\"128GB\",\"Color\":\"Black\"}', NULL, NULL, 607.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(22, 2, 'PHONE-001-128GB-NAVY', NULL, '128GB - Navy', '{\"Storage Capacity\":\"128GB\",\"Color\":\"Navy\"}', NULL, NULL, 598.99, 509.14, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(23, 2, 'PHONE-001-128GB-OLIVE', NULL, '128GB - Olive', '{\"Storage Capacity\":\"128GB\",\"Color\":\"Olive\"}', NULL, NULL, 599.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 2, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(24, 2, 'PHONE-001-128GB-WHITE', NULL, '128GB - White', '{\"Storage Capacity\":\"128GB\",\"Color\":\"White\"}', NULL, NULL, 605.99, 515.09, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 3, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(25, 2, 'PHONE-001-256GB-BLACK', NULL, '256GB - Black', '{\"Storage Capacity\":\"256GB\",\"Color\":\"Black\"}', NULL, NULL, 599.99, 509.99, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 4, '2025-11-27 02:18:33', '2025-11-27 02:20:54'),
(26, 2, 'PHONE-001-256GB-NAVY', NULL, '256GB - Navy', '{\"Storage Capacity\":\"256GB\",\"Color\":\"Navy\"}', NULL, NULL, 607.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 5, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(27, 2, 'PHONE-001-256GB-OLIVE', NULL, '256GB - Olive', '{\"Storage Capacity\":\"256GB\",\"Color\":\"Olive\"}', NULL, NULL, 599.99, 509.99, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 6, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(28, 2, 'PHONE-001-256GB-WHITE', NULL, '256GB - White', '{\"Storage Capacity\":\"256GB\",\"Color\":\"White\"}', NULL, NULL, 601.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 7, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(29, 2, 'PHONE-001-64GB-BLACK', NULL, '64GB - Black', '{\"Storage Capacity\":\"64GB\",\"Color\":\"Black\"}', NULL, NULL, 594.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 8, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(30, 2, 'PHONE-001-64GB-NAVY', NULL, '64GB - Navy', '{\"Storage Capacity\":\"64GB\",\"Color\":\"Navy\"}', NULL, NULL, 601.99, 511.69, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 9, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(31, 2, 'PHONE-001-64GB-OLIVE', NULL, '64GB - Olive', '{\"Storage Capacity\":\"64GB\",\"Color\":\"Olive\"}', NULL, NULL, 604.99, 514.24, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 10, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(32, 2, 'PHONE-001-64GB-WHITE', NULL, '64GB - White', '{\"Storage Capacity\":\"64GB\",\"Color\":\"White\"}', NULL, NULL, 598.99, 509.14, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 11, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(33, 3, 'SHOE-001-UK7-BLACK', NULL, 'UK7 - Black', '{\"Shoe Size\":\"UK7\",\"Color\":\"Black\"}', NULL, NULL, 82.99, 70.54, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(34, 3, 'SHOE-001-UK7-NAVY', NULL, 'UK7 - Navy', '{\"Shoe Size\":\"UK7\",\"Color\":\"Navy\"}', NULL, NULL, 88.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(35, 3, 'SHOE-001-UK7-OLIVE', NULL, 'UK7 - Olive', '{\"Shoe Size\":\"UK7\",\"Color\":\"Olive\"}', NULL, NULL, 78.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 2, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(36, 3, 'SHOE-001-UK7-WHITE', NULL, 'UK7 - White', '{\"Shoe Size\":\"UK7\",\"Color\":\"White\"}', NULL, NULL, 87.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 3, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(37, 3, 'SHOE-001-UK8-BLACK', NULL, 'UK8 - Black', '{\"Shoe Size\":\"UK8\",\"Color\":\"Black\"}', NULL, NULL, 84.99, 72.24, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 4, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(38, 3, 'SHOE-001-UK8-NAVY', NULL, 'UK8 - Navy', '{\"Shoe Size\":\"UK8\",\"Color\":\"Navy\"}', NULL, NULL, 78.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 5, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(39, 3, 'SHOE-001-UK8-OLIVE', NULL, 'UK8 - Olive', '{\"Shoe Size\":\"UK8\",\"Color\":\"Olive\"}', NULL, NULL, 80.99, 68.84, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 6, '2025-11-27 02:18:33', '2025-11-27 02:20:55'),
(40, 3, 'SHOE-001-UK8-WHITE', NULL, 'UK8 - White', '{\"Shoe Size\":\"UK8\",\"Color\":\"White\"}', NULL, NULL, 79.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 7, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(41, 3, 'SHOE-001-UK9-BLACK', NULL, 'UK9 - Black', '{\"Shoe Size\":\"UK9\",\"Color\":\"Black\"}', NULL, NULL, 88.99, 75.64, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 8, '2025-11-27 02:18:33', '2025-11-27 02:20:55'),
(42, 3, 'SHOE-001-UK9-NAVY', NULL, 'UK9 - Navy', '{\"Shoe Size\":\"UK9\",\"Color\":\"Navy\"}', NULL, NULL, 88.99, 75.64, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 9, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(43, 3, 'SHOE-001-UK9-OLIVE', NULL, 'UK9 - Olive', '{\"Shoe Size\":\"UK9\",\"Color\":\"Olive\"}', NULL, NULL, 83.99, 71.39, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 10, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(44, 3, 'SHOE-001-UK9-WHITE', NULL, 'UK9 - White', '{\"Shoe Size\":\"UK9\",\"Color\":\"White\"}', NULL, NULL, 83.99, 71.39, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 11, '2025-11-27 02:18:33', '2025-11-27 02:22:12'),
(45, 4, 'DRESS-001-BLACK-L', NULL, 'Black - L', '{\"Color\":\"Black\",\"Clothing Size\":\"L\"}', NULL, NULL, 53.99, 45.89, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(46, 4, 'DRESS-001-BLACK-M', NULL, 'Black - M', '{\"Color\":\"Black\",\"Clothing Size\":\"M\"}', NULL, NULL, 46.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(47, 4, 'DRESS-001-BLACK-S', NULL, 'Black - S', '{\"Color\":\"Black\",\"Clothing Size\":\"S\"}', NULL, NULL, 57.99, 49.29, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 2, '2025-11-27 02:18:34', '2025-11-27 02:20:55'),
(48, 4, 'DRESS-001-BLACK-XL', NULL, 'Black - XL', '{\"Color\":\"Black\",\"Clothing Size\":\"XL\"}', NULL, NULL, 48.99, 41.64, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 3, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(49, 4, 'DRESS-001-BLACK-XXL', NULL, 'Black - XXL', '{\"Color\":\"Black\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 48.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 4, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(50, 4, 'DRESS-001-NAVY-L', NULL, 'Navy - L', '{\"Color\":\"Navy\",\"Clothing Size\":\"L\"}', NULL, NULL, 55.99, 47.59, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 5, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(51, 4, 'DRESS-001-NAVY-M', NULL, 'Navy - M', '{\"Color\":\"Navy\",\"Clothing Size\":\"M\"}', NULL, NULL, 54.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 6, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(52, 4, 'DRESS-001-NAVY-S', NULL, 'Navy - S', '{\"Color\":\"Navy\",\"Clothing Size\":\"S\"}', NULL, NULL, 58.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 7, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(53, 4, 'DRESS-001-NAVY-XL', NULL, 'Navy - XL', '{\"Color\":\"Navy\",\"Clothing Size\":\"XL\"}', NULL, NULL, 59.99, 50.99, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 8, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(54, 4, 'DRESS-001-NAVY-XXL', NULL, 'Navy - XXL', '{\"Color\":\"Navy\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 57.99, 49.29, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 9, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(55, 4, 'DRESS-001-OLIVE-L', NULL, 'Olive - L', '{\"Color\":\"Olive\",\"Clothing Size\":\"L\"}', NULL, NULL, 47.99, 40.79, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 10, '2025-11-27 02:18:34', '2025-11-27 02:20:55'),
(56, 4, 'DRESS-001-OLIVE-M', NULL, 'Olive - M', '{\"Color\":\"Olive\",\"Clothing Size\":\"M\"}', NULL, NULL, 58.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 11, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(57, 4, 'DRESS-001-OLIVE-S', NULL, 'Olive - S', '{\"Color\":\"Olive\",\"Clothing Size\":\"S\"}', NULL, NULL, 48.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 12, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(58, 4, 'DRESS-001-OLIVE-XL', NULL, 'Olive - XL', '{\"Color\":\"Olive\",\"Clothing Size\":\"XL\"}', NULL, NULL, 50.99, 43.34, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 13, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(59, 4, 'DRESS-001-OLIVE-XXL', NULL, 'Olive - XXL', '{\"Color\":\"Olive\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 47.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 14, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(60, 4, 'DRESS-001-WHITE-L', NULL, 'White - L', '{\"Color\":\"White\",\"Clothing Size\":\"L\"}', NULL, NULL, 47.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 15, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(61, 4, 'DRESS-001-WHITE-M', NULL, 'White - M', '{\"Color\":\"White\",\"Clothing Size\":\"M\"}', NULL, NULL, 45.99, 39.09, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 16, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(62, 4, 'DRESS-001-WHITE-S', NULL, 'White - S', '{\"Color\":\"White\",\"Clothing Size\":\"S\"}', NULL, NULL, 56.99, 48.44, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 17, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(63, 4, 'DRESS-001-WHITE-XL', NULL, 'White - XL', '{\"Color\":\"White\",\"Clothing Size\":\"XL\"}', NULL, NULL, 53.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 18, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(64, 4, 'DRESS-001-WHITE-XXL', NULL, 'White - XXL', '{\"Color\":\"White\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 48.99, 41.64, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 19, '2025-11-27 02:18:34', '2025-11-27 02:22:12'),
(65, 5, 'PEND-001', NULL, 'Silver Vertical Bar Pendant for Men', '[]', NULL, NULL, 89.99, 69.99, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:18:34', '2025-11-27 02:18:34'),
(66, 6, 'WALL-001', NULL, 'Leather Wallet', '[]', NULL, NULL, 45.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:18:34', '2025-11-27 02:18:34'),
(67, 7, 'BOTT-001', NULL, 'Stainless Steel Water Bottle', '[]', NULL, NULL, 24.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(68, 8, 'EARB-001', NULL, 'Wireless Earbuds', '[]', NULL, NULL, 129.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(69, 9, 'JEAN-001-BLACK-L', NULL, 'Black - L', '{\"Color\":\"Black\",\"Clothing Size\":\"L\"}', NULL, NULL, 58.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(70, 9, 'JEAN-001-BLACK-M', NULL, 'Black - M', '{\"Color\":\"Black\",\"Clothing Size\":\"M\"}', NULL, NULL, 66.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(71, 9, 'JEAN-001-BLACK-S', NULL, 'Black - S', '{\"Color\":\"Black\",\"Clothing Size\":\"S\"}', NULL, NULL, 59.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 2, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(72, 9, 'JEAN-001-BLACK-XL', NULL, 'Black - XL', '{\"Color\":\"Black\",\"Clothing Size\":\"XL\"}', NULL, NULL, 65.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 3, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(73, 9, 'JEAN-001-BLACK-XXL', NULL, 'Black - XXL', '{\"Color\":\"Black\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 57.99, 49.29, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 4, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(74, 9, 'JEAN-001-NAVY-L', NULL, 'Navy - L', '{\"Color\":\"Navy\",\"Clothing Size\":\"L\"}', NULL, NULL, 56.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 5, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(75, 9, 'JEAN-001-NAVY-M', NULL, 'Navy - M', '{\"Color\":\"Navy\",\"Clothing Size\":\"M\"}', NULL, NULL, 57.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 6, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(76, 9, 'JEAN-001-NAVY-S', NULL, 'Navy - S', '{\"Color\":\"Navy\",\"Clothing Size\":\"S\"}', NULL, NULL, 65.99, 56.09, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 7, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(77, 9, 'JEAN-001-NAVY-XL', NULL, 'Navy - XL', '{\"Color\":\"Navy\",\"Clothing Size\":\"XL\"}', NULL, NULL, 69.99, 59.49, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 8, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(78, 9, 'JEAN-001-NAVY-XXL', NULL, 'Navy - XXL', '{\"Color\":\"Navy\",\"Clothing Size\":\"XXL\"}', NULL, NULL, 59.99, 50.99, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 9, '2025-11-27 02:22:12', '2025-11-27 02:22:12'),
(79, 10, 'LAPT-001-128GB', NULL, '128GB', '{\"Storage Capacity\":\"128GB\"}', NULL, NULL, 906.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(80, 10, 'LAPT-001-256GB', NULL, '256GB', '{\"Storage Capacity\":\"256GB\"}', NULL, NULL, 906.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(81, 10, 'LAPT-001-64GB', NULL, '64GB', '{\"Storage Capacity\":\"64GB\"}', NULL, NULL, 908.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 2, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(82, 11, 'BACK-001', NULL, 'Canvas Backpack', '[]', NULL, NULL, 39.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(83, 12, 'FITN-001', NULL, 'Fitness Tracker', '[]', NULL, NULL, 79.99, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1, 0, 0, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-11-27 02:22:13', '2025-11-27 02:22:13'),
(84, 14, 'GREY-1-GRE', NULL, 'Grey', '{\"6\":\"Grey\"}', '[{\"attribute_id\":7,\"attribute_name\":\"Chest Size\",\"attribute_slug\":\"chest-size\",\"value\":42,\"unit_id\":9,\"unit_name\":\"Inches\",\"unit_symbol\":\"in\",\"unit_type\":\"length\"}]', '[]', 799.00, 799.00, NULL, 'products/variants/8wVuI8YXGzXNbawUJMpAcdp2CC64bYwEkWAfz2oX.png', NULL, NULL, 0, NULL, NULL, 0, 238, 12, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 0, '2025-12-02 06:57:49', '2025-12-12 07:24:43'),
(86, 14, 'GREYCOLLAR-2-NAV', NULL, 'Navy', '{\"6\":\"Navy\"}', '[{\"attribute_id\":7,\"attribute_name\":\"Chest Size\",\"attribute_slug\":\"chest-size\",\"value\":70,\"unit_id\":9,\"unit_name\":\"Inches\",\"unit_symbol\":\"in\",\"unit_type\":\"length\"}]', '[{\"heading_name\":\"heading name\",\"bullet_points\":[\"test\"]},{\"heading_name\":\"heading 2\",\"bullet_points\":[\"heading 2 points\"]}]', 500.00, 500.00, NULL, 'products/variants/MZX21VGqmYEmPRz8w9cJg4b9hxAy1E6OxQFIJYJj.jpg', NULL, NULL, 0, NULL, NULL, 0, 460, 11, 'in_stock', NULL, NULL, NULL, NULL, NULL, 1, 1, '2025-12-09 05:55:18', '2025-12-12 07:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `level` varchar(255) NOT NULL DEFAULT 'standard',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `parent_id`, `level`, `is_active`, `is_system`, `sort_order`, `metadata`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', NULL, 'standard', 1, 0, 0, NULL, 'Has full access to all features and settings', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(2, 'Manager', 'manager', NULL, 'standard', 1, 0, 0, NULL, 'Can manage products, orders, and customers', '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(3, 'Editor88     iu', 'editor88-iu', NULL, 'standard', 1, 0, 0, NULL, 'Can manage content like products and categories', '2025-10-16 06:17:39', '2025-11-11 05:51:05'),
(4, 'Viewer', 'viewer', NULL, 'standard', 1, 0, 0, NULL, 'Can only view data, no editing permissions', '2025-10-16 06:17:39', '2025-10-16 06:17:39');

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_user`
--

INSERT INTO `role_user` (`id`, `role_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2025-10-16 06:17:39', '2025-10-16 06:17:39'),
(2, 3, 2, '2025-11-20 23:05:17', '2025-11-20 23:05:17');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `page_id`, `section_id`, `content`, `image`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'banner_variation_1', NULL, NULL, 1, 0, '2025-11-14 23:51:50', '2025-11-15 00:41:06'),
(2, 1, 'banner_variation_2', NULL, NULL, 1, 1, '2025-11-14 23:52:03', '2025-11-15 00:41:06'),
(3, 1, 'banner_variation_3', NULL, NULL, 1, 0, '2025-11-14 23:52:09', '2025-11-15 00:41:06'),
(4, 1, 'popular_products_variation_1', NULL, 'sections/9deCtNs4ATCSwSmTjYVeZ9dTfwuC88868LRXjvWY.jpg', 0, 1, '2025-11-15 00:40:23', '2025-11-15 00:44:55'),
(5, 1, 'popular_products_variation_2', NULL, NULL, 0, 0, '2025-11-15 00:40:23', '2025-11-15 00:42:44'),
(6, 1, 'new_arrivals_variation_1', NULL, NULL, 2, 0, '2025-11-15 00:40:23', '2025-11-15 00:41:06'),
(7, 1, 'new_arrivals_variation_2', NULL, NULL, 2, 1, '2025-11-15 00:40:23', '2025-11-15 00:41:06'),
(8, 1, 'discount_variation_1', NULL, NULL, 3, 1, '2025-11-15 00:40:23', '2025-11-15 00:45:13'),
(9, 1, 'discount_variation_2', NULL, NULL, 3, 0, '2025-11-15 00:40:23', '2025-11-15 00:45:13'),
(10, 1, 'brand_logos', NULL, NULL, 4, 0, '2025-11-15 00:40:23', '2025-11-15 00:41:06'),
(11, 1, 'blogs', NULL, NULL, 5, 1, '2025-11-15 00:40:23', '2025-11-15 00:52:18'),
(12, 1, 'service_highlight', NULL, NULL, 6, 1, '2025-11-15 00:40:23', '2025-11-15 00:52:16'),
(13, 1, 'banner_slider_variation_1', NULL, NULL, 7, 0, '2025-11-15 00:52:05', '2025-11-15 00:52:05'),
(14, 1, 'banner_slider_variation_2', NULL, NULL, 7, 0, '2025-11-15 00:52:05', '2025-11-15 00:52:05'),
(15, 1, 'banner_slider_variation_3', NULL, NULL, 7, 0, '2025-11-15 00:52:05', '2025-11-15 00:52:05');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('pNRWHuVxs6j21Nzsxpws5G2zxOUpi23IIpQNrfmX', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMkZYY2NRd3M1d211c3FEWFh1YzZHckZIcEV4SWhBSExEVEZ1QWx1WSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9pbnZlbnRvcnkiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1765544083);

-- --------------------------------------------------------

--
-- Table structure for table `shipping_methods`
--

CREATE TABLE `shipping_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `estimated_days_min` int(11) DEFAULT NULL,
  `estimated_days_max` int(11) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_methods`
--

INSERT INTO `shipping_methods` (`id`, `name`, `code`, `description`, `estimated_days_min`, `estimated_days_max`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Standard shipping', 'STANDARD', NULL, 3, 5, 'active', 1, '2025-12-12 06:58:14', '2025-12-12 06:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_rates`
--

CREATE TABLE `shipping_rates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shipping_zone_id` bigint(20) UNSIGNED NOT NULL,
  `shipping_method_id` bigint(20) UNSIGNED NOT NULL,
  `rate_type` enum('flat_rate','weight_based','price_based','distance_based') NOT NULL DEFAULT 'flat_rate',
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rate_per_kg` decimal(10,2) DEFAULT NULL,
  `rate_percentage` decimal(5,2) DEFAULT NULL,
  `min_value` decimal(10,2) DEFAULT NULL,
  `max_value` decimal(10,2) DEFAULT NULL,
  `fragile_surcharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `oversized_surcharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `hazardous_surcharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `express_surcharge` decimal(10,2) NOT NULL DEFAULT 0.00,
  `free_shipping_threshold` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_rates`
--

INSERT INTO `shipping_rates` (`id`, `shipping_zone_id`, `shipping_method_id`, `rate_type`, `rate`, `rate_per_kg`, `rate_percentage`, `min_value`, `max_value`, `fragile_surcharge`, `oversized_surcharge`, `hazardous_surcharge`, `express_surcharge`, `free_shipping_threshold`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'flat_rate', 50.00, NULL, NULL, NULL, NULL, 20.00, 50.00, 100.00, 0.00, 1000.00, 'active', '2025-12-12 07:07:42', '2025-12-12 07:07:42');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_zones`
--

CREATE TABLE `shipping_zones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` enum('pincode','state','city','country') NOT NULL DEFAULT 'pincode',
  `description` text DEFAULT NULL,
  `pincodes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`pincodes`)),
  `states` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`states`)),
  `cities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`cities`)),
  `country` varchar(255) NOT NULL DEFAULT 'India',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipping_zones`
--

INSERT INTO `shipping_zones` (`id`, `name`, `code`, `type`, `description`, `pincodes`, `states`, `cities`, `country`, `status`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'delhi zone', 'delhi1', 'pincode', NULL, '[\"110001\",\"110002\",\"110003\",\"110004\",\"110005\",\"110006\",\"110007\",\"110008\",\"110009\",\"110010\",\"110011\",\"110012\",\"110013\",\"110014\",\"110015\",\"110016\",\"110017\",\"110018\",\"110019\",\"110020\"]', NULL, NULL, 'India', 'active', 1, '2025-12-12 06:44:37', '2025-12-12 07:03:26');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `symbol` varchar(255) NOT NULL,
  `abbreviation` varchar(255) DEFAULT NULL,
  `type` enum('length','weight','volume','time','temperature','area','angle','energy','pressure','electric','frequency','other') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `conversion_factor` decimal(20,8) NOT NULL DEFAULT 1.00000000,
  `base_unit` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `symbol`, `abbreviation`, `type`, `description`, `conversion_factor`, `base_unit`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Kilogram', 'kg', NULL, 'weight', NULL, 1.00000000, NULL, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(2, 'Gram', 'g', NULL, 'weight', NULL, 1.00000000, NULL, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(3, 'Centimeter', 'cm', NULL, 'length', NULL, 1.00000000, NULL, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(4, 'Meter', 'm', NULL, 'length', NULL, 1.00000000, NULL, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(5, 'Liter', 'L', NULL, 'volume', NULL, 1.00000000, NULL, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(6, 'Milliliter', 'ml', NULL, 'volume', NULL, 1.00000000, NULL, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(7, 'Piece', 'pc', NULL, 'other', NULL, 1.00000000, NULL, 1, 0, '2025-11-10 06:59:41', '2025-11-10 06:59:41'),
(8, 'Milligram', 'mg', NULL, 'weight', NULL, 1.00000000, NULL, 1, 0, '2025-11-11 02:13:56', '2025-11-11 02:13:56'),
(9, 'Inches', 'in', NULL, 'length', NULL, 1.00000000, NULL, 1, 0, '2025-12-02 06:45:01', '2025-12-02 06:45:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `image`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@gmail.com', 'profile-images/p8czJicvrCcDFKKgdki0ZXfAlprLHcOGNBVabI0R.jpg', '2025-10-16 06:17:39', '$2y$12$1CME5SpvON4QThCQVYN7zO.ivOHTxMaMtnZWFPvJ3VyhtXjPILb92', NULL, '2025-10-16 06:17:39', '2025-11-21 01:57:04'),
(2, 'Gaurav', 'Gaurav@gmail.com', 'profile-images/1qKux45EhBbUtCAQlVVFKLE1XHy5FoW3lRPDvOni.jpg', NULL, '$2y$12$H92TLmeG7z9ouPByuGYdaOfGJOnASo6BWoHRLMBt0Qd8YSbriSZSy', NULL, '2025-11-20 23:05:17', '2025-11-20 23:05:17');

-- --------------------------------------------------------

--
-- Table structure for table `variant_heading_suggestions`
--

CREATE TABLE `variant_heading_suggestions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `heading_name` varchar(255) NOT NULL,
  `usage_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `country` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `code`, `address`, `city`, `state`, `country`, `status`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'test', 'WH22', 'warehouse adddres', 'sample city', 'sample satate', 'sample country', 'active', 1, '2025-12-12 03:48:57', '2025-12-12 05:06:52'),
(2, 'Warehouse location 2', 'Warehouse2', 'warehouse 2 address', 'warehouse city', 'warehouse state', 'warehouse country', 'active', 0, '2025-12-12 05:06:40', '2025-12-12 05:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_locations`
--

CREATE TABLE `warehouse_locations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `rack` varchar(255) DEFAULT NULL,
  `shelf` varchar(255) DEFAULT NULL,
  `bin` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouse_locations`
--

INSERT INTO `warehouse_locations` (`id`, `warehouse_id`, `rack`, `shelf`, `bin`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Rack-A', 'Shelf-1', 'Bin-A', 'active', '2025-12-12 04:19:15', '2025-12-12 04:19:15'),
(2, 1, 'Rack-A', 'Shelf-B', 'Bin-B', 'active', '2025-12-12 04:34:58', '2025-12-12 04:34:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brands_slug_unique` (`slug`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_customer_id_foreign` (`customer_id`),
  ADD KEY `carts_session_id_customer_id_index` (`session_id`,`customer_id`),
  ADD KEY `carts_expires_at_index` (`expires_at`),
  ADD KEY `carts_session_id_index` (`session_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`cart_id`,`product_id`,`product_variant_id`),
  ADD KEY `cart_items_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `cart_items_cart_id_index` (`cart_id`),
  ADD KEY `cart_items_product_id_product_variant_id_index` (`product_id`,`product_variant_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_deleted_at_unique` (`slug`,`deleted_at`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `category_attributes`
--
ALTER TABLE `category_attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `category_attributes_category_id_slug_unique` (`category_id`,`slug`),
  ADD KEY `category_attributes_category_id_sort_order_index` (`category_id`,`sort_order`);

--
-- Indexes for table `category_product_attribute`
--
ALTER TABLE `category_product_attribute`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cat_prod_attr_unique` (`category_id`,`product_attribute_id`),
  ADD KEY `category_product_attribute_product_attribute_id_foreign` (`product_attribute_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_code_index` (`code`),
  ADD KEY `coupons_status_index` (`status`),
  ADD KEY `coupons_start_date_end_date_index` (`start_date`,`end_date`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_email_unique` (`email`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_addresses_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `field_management_fields`
--
ALTER TABLE `field_management_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `field_management_fields_field_key_unique` (`field_key`);

--
-- Indexes for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_stock_location` (`product_variant_id`,`warehouse_id`,`warehouse_location_id`),
  ADD KEY `inventory_stocks_product_variant_id_index` (`product_variant_id`),
  ADD KEY `inventory_stocks_warehouse_id_index` (`warehouse_id`),
  ADD KEY `inventory_stocks_warehouse_location_id_index` (`warehouse_location_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leads_status_id_index` (`status_id`),
  ADD KEY `leads_source_id_index` (`source_id`),
  ADD KEY `leads_assigned_to_index` (`assigned_to`),
  ADD KEY `leads_priority_index` (`priority`),
  ADD KEY `leads_created_at_index` (`created_at`);

--
-- Indexes for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_activities_next_action_owner_foreign` (`next_action_owner`),
  ADD KEY `lead_activities_lead_id_index` (`lead_id`),
  ADD KEY `lead_activities_type_index` (`type`),
  ADD KEY `lead_activities_follow_up_date_index` (`follow_up_date`),
  ADD KEY `lead_activities_created_by_index` (`created_by`);

--
-- Indexes for table `lead_priorities`
--
ALTER TABLE `lead_priorities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_priorities_slug_unique` (`slug`);

--
-- Indexes for table `lead_reminders`
--
ALTER TABLE `lead_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_reminders_created_by_foreign` (`created_by`),
  ADD KEY `lead_reminders_lead_id_index` (`lead_id`),
  ADD KEY `lead_reminders_reminder_date_index` (`reminder_date`),
  ADD KEY `lead_reminders_is_completed_index` (`is_completed`),
  ADD KEY `lead_reminders_assigned_to_index` (`assigned_to`);

--
-- Indexes for table `lead_sources`
--
ALTER TABLE `lead_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_sources_slug_unique` (`slug`);

--
-- Indexes for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_statuses_slug_unique` (`slug`);

--
-- Indexes for table `lead_tag`
--
ALTER TABLE `lead_tag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_tag_lead_id_lead_tag_id_unique` (`lead_id`,`lead_tag_id`),
  ADD KEY `lead_tag_lead_id_index` (`lead_id`),
  ADD KEY `lead_tag_lead_tag_id_index` (`lead_tag_id`);

--
-- Indexes for table `lead_tags`
--
ALTER TABLE `lead_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_tags_slug_unique` (`slug`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `order_items_warehouse_location_id_foreign` (`warehouse_location_id`),
  ADD KEY `order_items_warehouse_id_index` (`warehouse_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`),
  ADD UNIQUE KEY `pages_url_unique` (`url`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`),
  ADD KEY `permissions_module_resource_action_index` (`module`,`resource`,`action`),
  ADD KEY `permissions_module_is_active_index` (`module`,`is_active`),
  ADD KEY `permissions_module_index` (`module`),
  ADD KEY `permissions_resource_index` (`resource`),
  ADD KEY `permissions_action_index` (`action`),
  ADD KEY `permissions_group_index` (`group`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_role_permission_id_role_id_unique` (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `permission_user`
--
ALTER TABLE `permission_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_user_user_id_permission_id_unique` (`user_id`,`permission_id`),
  ADD KEY `permission_user_permission_id_foreign` (`permission_id`),
  ADD KEY `permission_user_user_id_granted_index` (`user_id`,`granted`),
  ADD KEY `permission_user_expires_at_index` (`expires_at`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_status_published_at_index` (`status`,`published_at`),
  ADD KEY `products_type_status_index` (`status`),
  ADD KEY `products_featured_index` (`featured`),
  ADD KEY `products_brand_id_type_index` (`brand_id`),
  ADD KEY `products_unit_id_foreign` (`unit_id`),
  ADD KEY `products_category_id_index` (`category_id`),
  ADD KEY `products_default_warehouse_id_index` (`default_warehouse_id`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_attributes_slug_unique` (`slug`),
  ADD KEY `product_attributes_is_variation_is_visible_index` (`is_variation`,`is_visible`),
  ADD KEY `product_attributes_sort_order_index` (`sort_order`);

--
-- Indexes for table `product_attribute_values`
--
ALTER TABLE `product_attribute_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_attribute_values_attribute_id_value_unique` (`attribute_id`,`value`),
  ADD KEY `product_attribute_values_attribute_id_sort_order_index` (`attribute_id`,`sort_order`);

--
-- Indexes for table `product_brands`
--
ALTER TABLE `product_brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_brands_product_id_brand_id_unique` (`product_id`,`brand_id`),
  ADD KEY `product_brands_product_id_is_primary_index` (`product_id`,`is_primary`),
  ADD KEY `product_brands_brand_id_is_primary_index` (`brand_id`,`is_primary`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_categories_product_id_category_id_unique` (`product_id`,`category_id`),
  ADD KEY `product_categories_category_id_is_primary_index` (`category_id`,`is_primary`);

--
-- Indexes for table `product_category_attribute_values`
--
ALTER TABLE `product_category_attribute_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pca_values_product_attr_unique` (`product_id`,`category_attribute_id`),
  ADD KEY `product_category_attribute_values_category_attribute_id_foreign` (`category_attribute_id`),
  ADD KEY `pca_values_product_attr_index` (`product_id`,`category_attribute_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`),
  ADD KEY `product_images_product_id_is_primary_index` (`product_id`,`is_primary`),
  ADD KEY `product_images_product_variant_id_index` (`product_variant_id`);

--
-- Indexes for table `product_static_attributes`
--
ALTER TABLE `product_static_attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_static_attributes_product_id_attribute_id_unique` (`product_id`,`attribute_id`),
  ADD KEY `product_static_attributes_product_id_index` (`product_id`),
  ADD KEY `product_static_attributes_attribute_id_index` (`attribute_id`),
  ADD KEY `product_static_attributes_value_id_index` (`value_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD KEY `product_variants_product_id_is_active_index` (`product_id`,`is_active`),
  ADD KEY `product_variants_product_id_sort_order_index` (`product_id`,`sort_order`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`),
  ADD KEY `roles_parent_id_foreign` (`parent_id`),
  ADD KEY `roles_level_index` (`level`),
  ADD KEY `roles_is_active_index` (`is_active`),
  ADD KEY `roles_is_system_index` (`is_system`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_user_role_id_user_id_unique` (`role_id`,`user_id`),
  ADD KEY `role_user_user_id_foreign` (`user_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sections_page_id_foreign` (`page_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipping_methods_code_unique` (`code`),
  ADD KEY `shipping_methods_code_index` (`code`),
  ADD KEY `shipping_methods_status_index` (`status`),
  ADD KEY `shipping_methods_sort_order_index` (`sort_order`);

--
-- Indexes for table `shipping_rates`
--
ALTER TABLE `shipping_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_zone_method_rate` (`shipping_zone_id`,`shipping_method_id`,`rate_type`),
  ADD KEY `shipping_rates_shipping_zone_id_index` (`shipping_zone_id`),
  ADD KEY `shipping_rates_shipping_method_id_index` (`shipping_method_id`),
  ADD KEY `shipping_rates_rate_type_index` (`rate_type`),
  ADD KEY `shipping_rates_status_index` (`status`);

--
-- Indexes for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipping_zones_code_unique` (`code`),
  ADD KEY `shipping_zones_code_index` (`code`),
  ADD KEY `shipping_zones_type_index` (`type`),
  ADD KEY `shipping_zones_status_index` (`status`),
  ADD KEY `shipping_zones_sort_order_index` (`sort_order`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `units_type_is_active_index` (`type`,`is_active`),
  ADD KEY `units_sort_order_index` (`sort_order`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `variant_heading_suggestions`
--
ALTER TABLE `variant_heading_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `variant_heading_suggestions_heading_name_unique` (`heading_name`),
  ADD KEY `variant_heading_suggestions_heading_name_index` (`heading_name`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `warehouses_code_unique` (`code`),
  ADD KEY `warehouses_code_index` (`code`),
  ADD KEY `warehouses_status_index` (`status`),
  ADD KEY `warehouses_is_default_index` (`is_default`);

--
-- Indexes for table `warehouse_locations`
--
ALTER TABLE `warehouse_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_locations_warehouse_id_index` (`warehouse_id`),
  ADD KEY `warehouse_locations_status_index` (`status`),
  ADD KEY `warehouse_locations_warehouse_id_rack_shelf_bin_index` (`warehouse_id`,`rack`,`shelf`,`bin`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `category_attributes`
--
ALTER TABLE `category_attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category_product_attribute`
--
ALTER TABLE `category_product_attribute`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `field_management_fields`
--
ALTER TABLE `field_management_fields`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lead_activities`
--
ALTER TABLE `lead_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lead_priorities`
--
ALTER TABLE `lead_priorities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lead_reminders`
--
ALTER TABLE `lead_reminders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_sources`
--
ALTER TABLE `lead_sources`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lead_statuses`
--
ALTER TABLE `lead_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lead_tag`
--
ALTER TABLE `lead_tag`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lead_tags`
--
ALTER TABLE `lead_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `permission_role`
--
ALTER TABLE `permission_role`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `permission_user`
--
ALTER TABLE `permission_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_attribute_values`
--
ALTER TABLE `product_attribute_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_brands`
--
ALTER TABLE `product_brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `product_category_attribute_values`
--
ALTER TABLE `product_category_attribute_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `product_static_attributes`
--
ALTER TABLE `product_static_attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_user`
--
ALTER TABLE `role_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `shipping_methods`
--
ALTER TABLE `shipping_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping_rates`
--
ALTER TABLE `shipping_rates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `shipping_zones`
--
ALTER TABLE `shipping_zones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `variant_heading_suggestions`
--
ALTER TABLE `variant_heading_suggestions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `warehouse_locations`
--
ALTER TABLE `warehouse_locations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_attributes`
--
ALTER TABLE `category_attributes`
  ADD CONSTRAINT `category_attributes_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_product_attribute`
--
ALTER TABLE `category_product_attribute`
  ADD CONSTRAINT `category_product_attribute_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_product_attribute_product_attribute_id_foreign` FOREIGN KEY (`product_attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  ADD CONSTRAINT `inventory_stocks_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_stocks_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_stocks_warehouse_location_id_foreign` FOREIGN KEY (`warehouse_location_id`) REFERENCES `warehouse_locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_source_id_foreign` FOREIGN KEY (`source_id`) REFERENCES `lead_sources` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_status_id_foreign` FOREIGN KEY (`status_id`) REFERENCES `lead_statuses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_activities`
--
ALTER TABLE `lead_activities`
  ADD CONSTRAINT `lead_activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_activities_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_activities_next_action_owner_foreign` FOREIGN KEY (`next_action_owner`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_reminders`
--
ALTER TABLE `lead_reminders`
  ADD CONSTRAINT `lead_reminders_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_reminders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `lead_reminders_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_tag`
--
ALTER TABLE `lead_tag`
  ADD CONSTRAINT `lead_tag_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_tag_lead_tag_id_foreign` FOREIGN KEY (`lead_tag_id`) REFERENCES `lead_tags` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_warehouse_location_id_foreign` FOREIGN KEY (`warehouse_location_id`) REFERENCES `warehouse_locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permission_user`
--
ALTER TABLE `permission_user`
  ADD CONSTRAINT `permission_user_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_default_warehouse_id_foreign` FOREIGN KEY (`default_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_attribute_values`
--
ALTER TABLE `product_attribute_values`
  ADD CONSTRAINT `product_attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipping_rates`
--
ALTER TABLE `shipping_rates`
  ADD CONSTRAINT `shipping_rates_shipping_method_id_foreign` FOREIGN KEY (`shipping_method_id`) REFERENCES `shipping_methods` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipping_rates_shipping_zone_id_foreign` FOREIGN KEY (`shipping_zone_id`) REFERENCES `shipping_zones` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouse_locations`
--
ALTER TABLE `warehouse_locations`
  ADD CONSTRAINT `warehouse_locations_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
