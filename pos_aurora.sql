-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Des 2025 pada 14.26
-- Versi server: 10.4.28-MariaDB
-- Versi PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_aurora`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `cafe_tables`
--

CREATE TABLE `cafe_tables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `outlet_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 1,
  `status` enum('available','occupied','reserved') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `cafe_tables`
--

INSERT INTO `cafe_tables` (`id`, `outlet_id`, `name`, `code`, `capacity`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Meja01', 'T01', 3, 'available', '2025-12-15 11:40:21', '2025-12-19 12:31:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Minuman', 'minum', 1, '2025-12-14 14:37:44', '2025-12-14 14:37:47'),
(2, 'Makanan', 'makan', 1, '2025-12-14 14:37:51', '2025-12-14 14:37:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `qrcode_token` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `birth_date`, `qrcode_token`, `created_at`, `updated_at`) VALUES
(1, 'Djamal usman', '0819', 'djamausman86@gmail.com', NULL, NULL, '2025-12-19 10:22:55', '2025-12-19 10:22:55'),
(2, 'Djamal usman', '0819', 'djamausman86@gmail.com', NULL, NULL, '2025-12-19 10:24:41', '2025-12-19 10:24:41'),
(3, 'Djamal usman', '0819', 'djamausman86@gmail.com', NULL, NULL, '2025-12-19 10:25:22', '2025-12-19 10:25:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `failed_jobs`
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
-- Struktur dari tabel `ingredients`
--

CREATE TABLE `ingredients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `outlet_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `min_stock` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `ingredients`
--

INSERT INTO `ingredients` (`id`, `outlet_id`, `name`, `unit`, `stock`, `min_stock`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Kopi Arabica', 'pcs', 10, 5, 1, '2025-12-14 06:16:04', '2025-12-14 06:16:04'),
(2, 1, 'Susu UHT', 'pcs', 3, 2, 1, '2025-12-14 06:53:30', '2025-12-15 11:22:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `jobs`
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
-- Struktur dari tabel `job_batches`
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
-- Struktur dari tabel `loyalty_points`
--

CREATE TABLE `loyalty_points` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `points` int(11) NOT NULL,
  `type` enum('earn','redeem') NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `loyalty_points`
--

INSERT INTO `loyalty_points` (`id`, `customer_id`, `order_id`, `points`, `type`, `description`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 7, 'earn', 'Pembelian order ORD-20251219-172522', '2025-12-19 12:31:43', '2025-12-19 12:31:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `outlet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL,
  `is_best_seller` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `image` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `outlet_id`, `code`, `name`, `description`, `price`, `is_best_seller`, `is_active`, `created_at`, `updated_at`, `image`) VALUES
(1, 2, 1, 'MNM01', 'Mix Bento With RIce', 'Perpaduan nasi putih hangat dengan nugget crispy yang\r\ndisajikan bersama saus sambal pedas-manis dan mayo creamy.\r\nDilengkapi selada segar untuk sensasi rasa yang lebih\r\nlengkap. Cocok sebagai pilihan menu cepat, praktis, dan\r\nmengenyangkan.Kopi Susu GulaAren1', 25000, 0, 1, '2025-12-14 12:07:00', '2025-12-19 01:15:47', '694509b34580d_23.png'),
(2, 2, 1, 'MNM02', 'Chicken Katsu With Rice', 'Chicken Katsu istimewa dengan potongan daging ayam\r\nberkualitas tinggi, dibalut panko halus dan digoreng\r\nmenggunakan teknik suhu presisi untuk menghasilkan\r\nkerenyahan elegan tanpa menyerap minyak berlebih. Disajikan\r\ndengan saus katsu artisan yang kaya rasa, menciptakan\r\npengalaman bersantap yang refined dan memanjakan lidah', 25000, 0, 1, '2025-12-14 12:13:49', '2025-12-19 01:14:14', '6945094750d92_23.png'),
(3, 2, 1, 'MKN01', 'Ayam Tulang Lunak', 'Nikmati kelezatan ayam goreng dengan tekstur sempurna—luar\r\nrenyah, dalam super lembut! Ayam pilihan dimasak dengan\r\nteknik khusus hingga tulangnya menjadi lunak dan bisa\r\ndimakan. Bumbunya meresap hingga ke dalam, menghadirkan\r\nperpaduan rasa gurih, aromatik, dan juicy di setiap gigitan.', 25000, 0, 1, '2025-12-19 01:09:53', '2025-12-19 01:09:53', '69450851ce566_23.png'),
(4, 2, 1, 'MKN02', 'Mix Platter', 'Nikmati Mix Platter pilihan dengan kentang goreng goldencrisp, sosis premium yang juicy, serta nugget berkualitas\r\ndengan lapisan renyah bertekstur halus. Dipadukan dengan\r\nduo saus racikan khusus untuk pengalaman camilan yang lebih\r\nmemuaskan dan elegan.', 25000, 0, 1, '2025-12-19 01:31:43', '2025-12-19 01:31:43', '69450d6fbecbd_7254784.png'),
(5, 1, 1, 'MNM03', 'Matcha', 'Matcha', 15000, 0, 1, '2025-12-19 01:32:42', '2025-12-19 01:32:42', '69450daaa13a1_7254784.png'),
(6, 1, 1, 'MNM04', 'kopi Gula Aren', 'Kopi dan Gula Pohon Aren', 20000, 0, 1, '2025-12-19 01:34:09', '2025-12-19 01:34:09', '69450e0142abe_7254784.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_12_05_150828_create_roles_table', 1),
(5, '2025_12_05_150915_add_role_id_to_users_table', 1),
(6, '2025_12_05_151655_create_navigation_menus_table', 1),
(7, '2025_12_05_151711_create_role_navigation_menu_table', 1),
(8, '2025_12_05_151735_create_outlets_table', 1),
(9, '2025_12_05_151819_create_cafe_tables_table', 1),
(10, '2025_12_05_151850_create_categories_table', 1),
(11, '2025_12_05_151912_create_menu_items_table', 1),
(12, '2025_12_05_151946_create_customers_table', 1),
(13, '2025_12_05_152010_create_ingredients_table', 1),
(14, '2025_12_05_152055_create_recipes_table', 1),
(15, '2025_12_05_152118_create_promotions_table', 1),
(16, '2025_12_05_152159_create_promotion_menu_items_table', 1),
(17, '2025_12_05_152247_create_orders_table', 1),
(18, '2025_12_05_152332_create_order_items_table', 1),
(19, '2025_12_05_152347_create_payments_table', 1),
(20, '2025_12_05_152401_create_stock_movements_table', 1),
(21, '2025_12_05_152416_create_loyalty_points_table', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `navigation_menus`
--

CREATE TABLE `navigation_menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `route_name` varchar(150) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `navigation_menus`
--

INSERT INTO `navigation_menus` (`id`, `parent_id`, `name`, `route_name`, `url`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Dashboard Owner', 'owner.dashboard', NULL, 'zmdi zmdi-home', 1, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(2, NULL, 'Master Data', NULL, NULL, 'zmdi zmdi-assignment', 2, 0, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(3, NULL, 'Outlet', 'owner.outlets.index', NULL, 'bi bi-shop', 1, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(4, NULL, 'Users', 'owner.users.index', NULL, 'bi bi-people', 2, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(5, NULL, 'Menu', 'admin.menu.index', NULL, 'bi bi-journal-check', 3, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(6, NULL, 'Bahan Baku', 'admin.ingredients.index', NULL, 'bi bi-box', 2, 0, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(7, NULL, 'Dashboard', 'admin.dashboard', NULL, 'bi bi-grid-fill', 1, 0, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(8, NULL, 'Promo', 'admin.promotions.index', NULL, 'bi bi-tag', 4, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(23, NULL, 'Stok In/Out', 'admin.stock-movements.index', NULL, 'bi bi-arrow-left-right', 5, 0, '2025-12-10 18:46:09', '2025-12-10 18:46:09'),
(24, NULL, 'Meja', 'admin.tables.index', NULL, 'bi bi-grid-1x2', 6, 1, '2025-12-10 18:46:09', '2025-12-10 18:46:09'),
(25, NULL, 'Kasir', 'admin.cashiers.index', NULL, 'bi bi-people', 7, 1, '2025-12-10 18:46:09', '2025-12-10 18:46:09'),
(26, NULL, 'Report', 'admin.reports.sales', NULL, 'bi bi-bar-chart-line', 1, 1, '2025-12-10 18:46:09', '2025-12-10 18:46:09'),
(27, NULL, 'Dashboard', 'kasir.dashboard', NULL, 'bi bi-speedometer2', 1, 1, '2025-12-15 19:33:48', '2025-12-15 19:33:48'),
(28, NULL, 'POS / Transaksi', 'kasir.orders.index', NULL, 'bi bi-pen-fill', 2, 1, '2025-12-15 19:33:48', '2025-12-15 19:33:48'),
(29, NULL, 'Status Meja', 'kasir.tables.index', NULL, 'bi bi-grid-3x3-gap', 3, 1, '2025-12-15 19:33:48', '2025-12-15 19:33:48'),
(30, NULL, 'Customer & Loyalty', 'kasir.customers.index', NULL, 'bi bi-people', 4, 1, '2025-12-15 19:33:48', '2025-12-15 19:33:48'),
(31, NULL, 'Promo Aktif', 'kasir.promotions.index', NULL, 'bi bi-tag', 5, 1, '2025-12-15 19:33:48', '2025-12-15 19:33:48'),
(32, NULL, 'Report Today', 'kasir.reports.today', NULL, 'bi bi-clipboard-data', 6, 1, '2025-12-15 19:33:48', '2025-12-15 19:33:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_code` varchar(50) NOT NULL,
  `outlet_id` bigint(20) UNSIGNED NOT NULL,
  `table_id` bigint(20) UNSIGNED DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cashier_id` bigint(20) UNSIGNED NOT NULL,
  `promotion_id` bigint(20) NOT NULL,
  `order_date` datetime NOT NULL,
  `order_type` enum('dine_in','take_away','delivery') NOT NULL DEFAULT 'dine_in',
  `status` enum('draft','paid','void','open') NOT NULL DEFAULT 'draft',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_status` varchar(20) NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `outlet_id`, `table_id`, `customer_id`, `cashier_id`, `promotion_id`, `order_date`, `order_type`, `status`, `subtotal`, `discount_total`, `grand_total`, `payment_status`, `created_at`, `updated_at`) VALUES
(2, 'ORD-20251219-172522', 1, 1, 3, 3, 1, '2025-12-19 17:25:22', 'dine_in', 'paid', 80000.00, 8000.00, 72000.00, 'paid', '2025-12-19 10:25:22', '2025-12-19 12:31:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `qty`, `price`, `discount`, `total`, `created_at`, `updated_at`) VALUES
(3, 2, 3, 1, 25000.00, 2500.00, 22500.00, '2025-12-19 10:25:22', '2025-12-19 10:25:22'),
(4, 2, 2, 1, 25000.00, 2500.00, 22500.00, '2025-12-19 10:25:22', '2025-12-19 10:25:22'),
(5, 2, 5, 2, 15000.00, 3000.00, 27000.00, '2025-12-19 10:25:22', '2025-12-19 10:25:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `outlets`
--

CREATE TABLE `outlets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `outlets`
--

INSERT INTO `outlets` (`id`, `code`, `name`, `address`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'KK01', 'Kedai Kribo', 'jalan raya', '0819', 1, '2025-12-14 08:04:33', '2025-12-14 08:04:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method` enum('cash','qris','transfer') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `ref_no` varchar(100) DEFAULT NULL,
  `paid_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `ref_no`, `paid_at`, `created_at`, `updated_at`) VALUES
(3, 2, 'cash', 72000.00, 'PAY-20251219193143-2', '2025-12-19 19:31:43', '2025-12-19 12:31:43', '2025-12-19 12:31:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `outlet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('percent','nominal') NOT NULL,
  `value` int(11) NOT NULL,
  `min_amount` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_loyalty` tinyint(1) NOT NULL DEFAULT 0,
  `min_orders` int(11) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `promotions`
--

INSERT INTO `promotions` (`id`, `outlet_id`, `name`, `type`, `value`, `min_amount`, `is_active`, `is_loyalty`, `min_orders`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'Promo December', 'percent', 10, 2000, 1, 0, 2, '2025-12-16', '2025-12-31', '2025-12-15 01:02:29', '2025-12-19 09:45:10'),
(2, 1, 'Promo Tahun Baru', 'nominal', 2000, 3000, 1, 0, 2, '2025-12-15', '2026-01-01', '2025-12-15 05:53:56', '2025-12-19 00:14:43');

-- --------------------------------------------------------

--
-- Struktur dari tabel `promotion_menu_items`
--

CREATE TABLE `promotion_menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `promotion_id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `promotion_menu_items`
--

INSERT INTO `promotion_menu_items` (`id`, `promotion_id`, `menu_item_id`, `created_at`, `updated_at`) VALUES
(4, 2, 1, '2025-12-15 06:48:27', '2025-12-15 06:48:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `recipes`
--

CREATE TABLE `recipes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `ingredient_id` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(12,3) NOT NULL,
  `note` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'owner', 'Pemilik usaha, akses penuh.', '2025-12-05 10:02:17', '2025-12-05 10:02:17'),
(2, 'admin', 'Admin, mengelola master data & laporan.', '2025-12-05 10:02:17', '2025-12-05 10:02:17'),
(3, 'kasir', 'Kasir, fokus ke transaksi & pelanggan.', '2025-12-05 10:02:17', '2025-12-05 10:02:17');

-- --------------------------------------------------------

--
-- Struktur dari tabel `role_navigation_menu`
--

CREATE TABLE `role_navigation_menu` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `navigation_menu_id` bigint(20) UNSIGNED NOT NULL,
  `can_view` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `role_navigation_menu`
--

INSERT INTO `role_navigation_menu` (`id`, `role_id`, `navigation_menu_id`, `can_view`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(2, 1, 2, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(3, 1, 3, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(4, 1, 4, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(5, 1, 5, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(6, 1, 6, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(7, 2, 7, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(8, 2, 2, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(9, 2, 5, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(10, 2, 6, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(11, 2, 8, 1, '2025-12-05 10:02:18', '2025-12-05 10:02:18'),
(26, 2, 23, 1, '2025-12-13 18:57:53', '2025-12-13 18:57:57'),
(27, 2, 24, 1, '2025-12-13 18:58:24', '2025-12-13 18:58:26'),
(28, 2, 25, 1, '2025-12-13 18:58:39', '2025-12-13 18:58:42'),
(29, 2, 26, 1, '2025-12-13 18:59:03', '2025-12-13 18:59:05'),
(30, 3, 27, 1, '2025-12-15 19:35:27', '2025-12-15 19:35:27'),
(31, 3, 28, 1, '2025-12-15 19:35:27', '2025-12-15 19:35:27'),
(32, 3, 29, 1, '2025-12-15 19:35:27', '2025-12-15 19:35:27'),
(33, 3, 30, 1, '2025-12-15 19:35:27', '2025-12-15 19:35:27'),
(34, 3, 31, 1, '2025-12-15 19:35:27', '2025-12-15 19:35:27'),
(35, 3, 32, 1, '2025-12-15 19:35:27', '2025-12-15 19:35:27');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sessions`
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
-- Dumping data untuk tabel `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('m73KuugXU2Hkj9cwHXtcgAjtCylwnqYfH7DgGcb7', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidGNUWVVVWkFtWktySGlERnRRaUZVNE45Wjd1YVk2QVFiOXNYR21wcSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9rYXNpci9vcmRlcnMvMiI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjM7fQ==', 1766174871),
('xhVRFDGzSnAHlpiYujdh4jzlSFu3Y82gAVpkGM91', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieThVRjVwVEIzS3lucEY0Q3d1VjdTemhGbm5Ha0VpUXZmWEN6TjBZcSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTozO30=', 1766174246);

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ingredient_id` bigint(20) UNSIGNED NOT NULL,
  `outlet_id` bigint(20) UNSIGNED NOT NULL,
  `movement_type` enum('in','out','adjust') NOT NULL,
  `qty` decimal(12,3) NOT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `ingredient_id`, `outlet_id`, `movement_type`, `qty`, `reference_type`, `reference_no`, `description`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'out', 1.300, 'PO', 'NoRef01', 'Stok keluar', '2025-12-15 11:21:05', '2025-12-15 11:22:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL DEFAULT 3,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `outlet_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `outlet_id`) VALUES
(1, 1, 'Owner POS', 'owner@pos.test', NULL, '$2y$12$qVoNLaAa3tDhyYLWvQ8TxuPQDoT7yxHIFszVDKNm505/EEwmzL74i', NULL, '2025-12-05 10:02:18', '2025-12-05 10:02:18', NULL),
(2, 2, 'Admin POS', 'admin@pos.test', NULL, '$2y$12$xfSlbqIgbs9MGoAaCxD56uvbMFefp2TSrznt/1JsMbb9hm.l6PdWK', NULL, '2025-12-05 10:02:18', '2025-12-05 10:02:18', NULL),
(3, 3, 'Kasir POS', 'kasir@pos.test', NULL, '$2y$12$NMx0r7XMsQbalZe6GqGyM.s0rs4TczkfY/2qr4MkRKaNOpmsdt0fO', 'eQd1sXg4sABjux2zNDxFYA8mVW1HbL0MFmaUdpHvZJZxWco8tgDC6S72qLXx', '2025-12-05 10:02:18', '2025-12-05 10:02:18', 1),
(4, 3, 'Test User', 'test@example.com', '2025-12-05 10:02:18', '$2y$12$ne7VuPBxfbJ102IR.rG9dueQ/g.uIR.aF0og0YRJsncS6vIg8KeU2', 'QiVEaS8BdT', '2025-12-05 10:02:19', '2025-12-05 10:02:19', NULL),
(5, 3, 'Ilham Fareno', 'admin@gmail.com', NULL, '$2y$12$4uiipc.7HpFgp7xCIURWke8202ZxhaT3wblPOP/IbFI05AywBD/qy', NULL, '2025-12-19 08:05:12', '2025-12-19 08:05:12', 1);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indeks untuk tabel `cafe_tables`
--
ALTER TABLE `cafe_tables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cafe_tables_outlet_id_foreign` (`outlet_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indeks untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ingredients_outlet_id_foreign` (`outlet_id`);

--
-- Indeks untuk tabel `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indeks untuk tabel `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loyalty_points_customer_id_foreign` (`customer_id`),
  ADD KEY `loyalty_points_order_id_foreign` (`order_id`);

--
-- Indeks untuk tabel `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menu_items_code_unique` (`code`),
  ADD KEY `menu_items_category_id_foreign` (`category_id`),
  ADD KEY `menu_items_outlet_id_foreign` (`outlet_id`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `navigation_menus`
--
ALTER TABLE `navigation_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `navigation_menus_parent_id_foreign` (`parent_id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`),
  ADD KEY `orders_outlet_id_foreign` (`outlet_id`),
  ADD KEY `orders_table_id_foreign` (`table_id`),
  ADD KEY `orders_customer_id_foreign` (`customer_id`),
  ADD KEY `orders_cashier_id_foreign` (`cashier_id`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_menu_item_id_foreign` (`menu_item_id`);

--
-- Indeks untuk tabel `outlets`
--
ALTER TABLE `outlets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `outlets_code_unique` (`code`);

--
-- Indeks untuk tabel `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

--
-- Indeks untuk tabel `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promotions_outlet_id_foreign` (`outlet_id`);

--
-- Indeks untuk tabel `promotion_menu_items`
--
ALTER TABLE `promotion_menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promotion_menu_items_promotion_id_menu_item_id_unique` (`promotion_id`,`menu_item_id`),
  ADD KEY `promotion_menu_items_menu_item_id_foreign` (`menu_item_id`);

--
-- Indeks untuk tabel `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipes_menu_item_id_foreign` (`menu_item_id`),
  ADD KEY `recipes_ingredient_id_foreign` (`ingredient_id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indeks untuk tabel `role_navigation_menu`
--
ALTER TABLE `role_navigation_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_navigation_menu_role_id_navigation_menu_id_unique` (`role_id`,`navigation_menu_id`),
  ADD KEY `role_navigation_menu_navigation_menu_id_foreign` (`navigation_menu_id`);

--
-- Indeks untuk tabel `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indeks untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_movements_ingredient_id_foreign` (`ingredient_id`),
  ADD KEY `stock_movements_outlet_id_foreign` (`outlet_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `cafe_tables`
--
ALTER TABLE `cafe_tables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `loyalty_points`
--
ALTER TABLE `loyalty_points`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT untuk tabel `navigation_menus`
--
ALTER TABLE `navigation_menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `outlets`
--
ALTER TABLE `outlets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `promotion_menu_items`
--
ALTER TABLE `promotion_menu_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `role_navigation_menu`
--
ALTER TABLE `role_navigation_menu`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `cafe_tables`
--
ALTER TABLE `cafe_tables`
  ADD CONSTRAINT `cafe_tables_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`);

--
-- Ketidakleluasaan untuk tabel `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`);

--
-- Ketidakleluasaan untuk tabel `loyalty_points`
--
ALTER TABLE `loyalty_points`
  ADD CONSTRAINT `loyalty_points_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `loyalty_points_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ketidakleluasaan untuk tabel `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `menu_items_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`);

--
-- Ketidakleluasaan untuk tabel `navigation_menus`
--
ALTER TABLE `navigation_menus`
  ADD CONSTRAINT `navigation_menus_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `navigation_menus` (`id`);

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_cashier_id_foreign` FOREIGN KEY (`cashier_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `orders_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`),
  ADD CONSTRAINT `orders_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `cafe_tables` (`id`);

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`),
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Ketidakleluasaan untuk tabel `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`);

--
-- Ketidakleluasaan untuk tabel `promotion_menu_items`
--
ALTER TABLE `promotion_menu_items`
  ADD CONSTRAINT `promotion_menu_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`),
  ADD CONSTRAINT `promotion_menu_items_promotion_id_foreign` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`);

--
-- Ketidakleluasaan untuk tabel `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ingredient_id_foreign` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`),
  ADD CONSTRAINT `recipes_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`);

--
-- Ketidakleluasaan untuk tabel `role_navigation_menu`
--
ALTER TABLE `role_navigation_menu`
  ADD CONSTRAINT `role_navigation_menu_navigation_menu_id_foreign` FOREIGN KEY (`navigation_menu_id`) REFERENCES `navigation_menus` (`id`),
  ADD CONSTRAINT `role_navigation_menu_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ingredient_id_foreign` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`),
  ADD CONSTRAINT `stock_movements_outlet_id_foreign` FOREIGN KEY (`outlet_id`) REFERENCES `outlets` (`id`);

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
