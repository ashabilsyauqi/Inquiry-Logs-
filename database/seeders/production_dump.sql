-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 01, 2026 at 11:36 PM
-- Server version: 11.4.13-MariaDB
-- PHP Version: 8.4.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sryyuqht_crm_difitech`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_lead_comparisons`
--

CREATE TABLE `ai_lead_comparisons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `report_date` date NOT NULL,
  `real_stage_counts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL ,
  `ai_stage_counts` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL ,
  `differences` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL ,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `brand_supervisors`
--

CREATE TABLE `brand_supervisors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `wa_account_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brand_supervisors`
--

INSERT INTO `brand_supervisors` (`id`, `user_id`, `wa_account_id`, `created_at`, `updated_at`) VALUES
(1, 3, 4, '2026-08-30 23:45:46', '2026-08-30 23:45:46'),
(3, 3, 7, '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(4, 3, 8, '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(5, 3, 9, '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(6, 3, 10, '2026-08-31 00:51:31', '2026-08-31 00:51:31');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
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
  `wa_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `stage` varchar(255) NOT NULL DEFAULT 'Inquiries',
  `ai_suggested_stage` varchar(255) DEFAULT NULL,
  `ai_concluded_stage` varchar(255) DEFAULT NULL,
  `ai_suggested_keyword` varchar(255) DEFAULT NULL,
  `ai_suggestion_reason` text DEFAULT NULL,
  `ai_suggested_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `assigned_user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `wa_account_id`, `name`, `phone`, `stage`, `ai_suggested_stage`, `ai_concluded_stage`, `ai_suggested_keyword`, `ai_suggestion_reason`, `ai_suggested_at`, `created_at`, `updated_at`, `notes`, `priority`, `assigned_user_id`) VALUES
(1, 4, 'Amanda Zubaidah', '255580937679063', 'SPAM', NULL, 'SPAM', NULL, NULL, NULL, '2026-08-19 03:56:36', '2026-08-28 02:45:25', NULL, 0, 4),
(2, 4, 'Wijaya Andreas', '274577343160505', 'Lead Masuk', 'Tanya Spek Produk', 'Tanya Spek Produk', '#tanya jawab', 'Percakapan terbaru berada pada tahap diskusi konsultasi dan tanya jawab produk.', '2026-08-28 02:55:27', '2026-08-20 03:40:37', '2026-09-01 02:04:06', NULL, 0, 4),
(3, 4, 'siswandi', '213215178940557', 'SPAM', NULL, 'SPAM', NULL, NULL, NULL, '2026-08-20 03:52:23', '2026-08-28 02:42:45', NULL, 0, 4),
(4, NULL, 'Ashabil', '234179300167691', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-21 05:41:10', '2026-08-26 00:43:43', NULL, 0, NULL),
(5, NULL, '6287871976694', '6287871976694', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-21 07:08:03', '2026-08-26 00:43:43', NULL, 0, NULL),
(6, 4, '78971597271125', '78971597271125', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-21 09:43:35', '2026-08-26 21:15:39', NULL, 0, 4),
(7, 4, 'Deden Iskandar', '30756277633265', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-22 02:12:10', '2026-08-26 21:15:39', 'masih no respon', 0, 4),
(8, 4, 'Husni', '199200650342433', 'Diskusi', NULL, 'Diskusi', NULL, NULL, NULL, '2026-08-22 05:02:34', '2026-08-28 02:42:10', 'masih no respon', 0, 4),
(9, 4, 'Abi Syahla & Shila', '57076239847635', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-22 17:57:14', '2026-08-26 21:15:39', NULL, 0, 4),
(10, 4, 'x', '232843682812131', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-22 22:02:49', '2026-08-26 21:15:39', NULL, 0, 4),
(11, 4, 'Wanto', '206149940969544', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-22 23:49:02', '2026-08-26 21:15:39', NULL, 0, 4),
(12, 4, '.', '40952915861625', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-23 05:37:59', '2026-08-26 21:15:39', NULL, 0, 4),
(13, 4, 'Lia', '119305668681943', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-23 05:41:45', '2026-08-26 21:15:39', NULL, 0, 4),
(14, 4, 'Suten', '245273284575451', 'SPAM', NULL, 'SPAM', NULL, NULL, NULL, '2026-08-23 23:47:05', '2026-08-28 02:36:27', NULL, 0, 4),
(15, 4, 'Bismillah', '201658009141455', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-24 20:07:41', '2026-08-26 21:15:39', NULL, 0, 4),
(16, 4, 'Ashabil', '144564505579721', 'SPAM', NULL, 'SPAM', NULL, NULL, NULL, '2026-08-28 02:34:34', '2026-08-28 02:34:55', NULL, 0, 4),
(17, 4, '+14285631701215', '14285631701215', 'SPAM', NULL, 'SPAM', NULL, NULL, NULL, '2026-08-28 02:35:58', '2026-08-28 02:35:58', NULL, 0, 4),
(18, 4, '+18593500643509', '18593500643509', 'SPAM', NULL, 'SPAM', NULL, NULL, NULL, '2026-08-28 02:36:10', '2026-08-28 02:36:10', NULL, 0, 4),
(19, 4, '+94966793658385', '94966793658385', 'Diskusi', NULL, 'Diskusi', NULL, NULL, NULL, '2026-08-28 02:41:17', '2026-08-28 02:41:17', NULL, 0, 4),
(20, 4, '+267882076356659', '267882076356659', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-28 02:45:00', '2026-08-28 02:45:00', NULL, 0, 4),
(21, 4, 'Den Bagoes', '203036106457112', 'Sudah dapat lokasi', 'Tanya Spek Produk', 'Tanya Spek Produk', '#tanya jawab', 'Percakapan terbaru berada pada tahap diskusi konsultasi dan tanya jawab produk.', '2026-08-30 23:46:57', '2026-08-28 03:51:35', '2026-08-30 23:46:57', NULL, 0, 4),
(22, 4, '+48438674739434', '48438674739434', 'Lead Masuk', 'Tanya Spek Produk', 'Tanya Spek Produk', '#tanya jawab', 'Percakapan terbaru berada pada tahap diskusi konsultasi dan tanya jawab produk.', '2026-08-28 17:45:53', '2026-08-28 04:00:06', '2026-08-28 17:45:53', NULL, 0, 4),
(23, 4, 'Aceng Ari', '253566329585826', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-28 04:52:05', '2026-08-28 04:52:05', NULL, 0, 4),
(24, 4, 'A. D', '97788385845368', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-28 05:20:43', '2026-08-28 05:20:43', NULL, 0, 4),
(25, 4, 'syekhmuhammadrlzky123', '66194220572703', 'Lead Masuk', 'Tanya Spek Produk', 'Tanya Spek Produk', '#tanya jawab', 'Percakapan terbaru berada pada tahap diskusi konsultasi dan tanya jawab produk.', '2026-08-30 17:30:25', '2026-08-29 08:25:53', '2026-08-30 17:30:25', NULL, 0, 4),
(26, 4, 'ai rodiah1976', '12416800800896', 'Tanya Harga', 'Tanya Spek Produk', 'Tanya Spek Produk', '#tanya jawab', 'Percakapan terbaru berada pada tahap diskusi konsultasi dan tanya jawab produk.', '2026-08-29 22:17:40', '2026-08-29 16:33:52', '2026-08-29 22:17:40', NULL, 0, 4),
(27, 4, 'muhamad mahesa', '17627284004962', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-08-30 23:07:41', '2026-08-30 23:07:41', NULL, 0, 4),
(28, 8, 'Annaz', '16479923740792', 'Lead Masuk', 'Kirim Penawaran', 'Kirim Penawaran', '#kirim penawaran', 'Percakapan terbaru membahas pengiriman penawaran/proposal (penawaran).', '2026-09-01 04:47:25', '2026-09-01 02:28:41', '2026-09-01 04:47:25', NULL, 0, 5),
(29, 8, 'Imane Ah Africa', '90298164224038', 'Lead Masuk', 'Kirim Penawaran', 'Kirim Penawaran', '#kirim penawaran', 'Percakapan terbaru membahas pengiriman penawaran/proposal (quotation).', '2026-09-01 08:19:11', '2026-09-01 04:19:23', '2026-09-01 08:19:11', NULL, 0, 5),
(30, 8, 'Azzuhra F H', '34789386113092', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-09-01 05:22:50', '2026-09-01 05:22:50', NULL, 0, 5),
(31, 8, '+38594827821120', '38594827821120', 'Lead Masuk', NULL, 'Lead Masuk', NULL, NULL, NULL, '2026-09-01 09:24:40', '2026-09-01 09:24:40', NULL, 0, 5);

-- --------------------------------------------------------

--
-- Table structure for table `lead_messages`
--

CREATE TABLE `lead_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lead_id` bigint(20) UNSIGNED NOT NULL,
  `sender` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `is_from_me` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_messages`
--

INSERT INTO `lead_messages` (`id`, `lead_id`, `sender`, `message`, `is_from_me`, `created_at`, `updated_at`) VALUES
(1, 1, '255580937679063', 'LIST AREA SAMEDAY JKT-BEKASI & LUAR KOTA (PAXEL) DIKIRIM DARI GADING:\n1. ⁠Lukman Kharish - Mounjaro 5mg - Payment 7 Agustus - PU Klinik tanggal 18 Agustus - Aizza\n2. Angie Witara - Mounjaro 5mg - Jakarta - pay 10 ag - O\n3. Tiffany - Mounjaro 2.5mg - jkt - pay 11 agust - Cintia\n4. ⁠⁠Rendi - Mounjaro 2.5mg - jkt - pay 11 ags - liana\n5. Vivi Irawaty - Wegovy 0,25 - PU Klinik - pay 11 Agustus - Poppy\n6. Edwin H - mounjaro 2.5mg - sameday - pay 11 agus - lia\n7. Ifada Zain - Mounjaro 2,5mg - Paxel - pay 11 agust - nandita\n8. vonny - mounjaro 2,5mg - pick up klinik - pay 11 agust - nandita\n9. Felicia - ACIP 2,5 mg - pay 11 ags - JKT - Tiara\n10. Dessy Jusuf - Mounjaro 5mg - pay 11 Agust - PAXEL JGJ - S\n11. ⁠Yurike - Mounjaro 2.5mg - pay 12 agust - Paxel Semarang - liana\n12. ⁠Aishah - Mounjaro 5mg - payment 12 Agustus - JKT - Sameday - Siska\n13. ⁠Maya Wardhana - Wegovy 1mg + Wegovyy 1mg - Pay 12 ag - Jakarta - O\n14. ⁠Nachi  - Mounjaro 5mg - pay 12 Agust - JKT - S\n15. ⁠Drg Farah Swastika - Mounjaro 5mg- Paxel Aceh Barat - pay 12 Agustus - Amanda \n16. ⁠Sitti Fatrah - Mounjaro 5mg - Paxel Counter Gorontalo - pay 13 agt - Liana\n17. ⁠Andita Destiarini - Mounjaro 2.5mg - PU Klinik - Pay 13 Aug - Hani\n18. ⁠Teng Vincent Jonathan - Wegovy 0,25 - PU Klinik - pay 13 agt - Poppy\n19. ⁠Rizky Pravianti - Mounjaro 2,5mg - sidoarjo paxel 13 agustus - lia\n20. ⁠Esmaralda Nurul Amany - Mounjaro 5mg - Pay 13 Agustus - Kota Baru, Jambi (Paxel) - Siska\n21. ⁠Putu gede Narendra SD - Mounjaro 2,5mg - Paxel bali - pay 13 agt - karin \n22. ⁠Nurul Fachirah - Wegovy 0.5mg - pay 13 Agust\n59. Herlina - Wegovy 0.5mg - pay 16 Agust - JKT - S\n60. Nadia Jasmine -  Mounjaro 5mg - pay 18 Agust - PAXEL BDG - S\n125. Kurniata Sekararum - Mounjaro 5mg - pay 18 Agust - PAXEL BDG - S\n126. Sandra - Mounjaro 2,5mg - Pay 18 Agust - JAKBAR - AMELIA\n127. Ari Delima - Wegovy 0,25 mg - pay 19 Agustus - paxel counter pekanbaru - Hani\n128. Rima M - Wegovy 0,25 mg - pay 19 Agustus - paxel medan - Hani\n129. Roy Stagg - Wegovy 2.4mg - Jakarta - pay 19 130. Lutfi Aziz - Wegovy 0.5mg - Paxel lampung - pay 19 ag - O\n131. Chairunisa Oktaviani - Wegovy 1mg - PU klinik - pay 19 ag - O (istri)\n\n9. Agreini Dwi Erza - Mounjaro 5mg - pay 16 Agust - TGR - S\n10. ⁠Sumiati - ACIP 2.5 mg - TGR - Tiara\n11. Nita - Mounjaro 2.5mg. -injek di bsd - pay 17 agust - cintia\n12. Maria Dewi - ACI 0,5 mg - pay 17 Agustus - Tangerang - Amanda \n13. Yasinda Arga - Wegovy 1.7mg - pay 17 Agust - DPK - S\n14. Gita Dwinta Sari - Wegovy 0.5mg - pay 17 Agust - DPK - S\n15. Dini Suryabrata - Wegovy 1.7mg - pay 17 Agust - TGR - S\n16. Eva Tanura - Wegovy 1mg - pay 17 Agust - TGR - S\n17. Liya - Wegovy 0.5mg - pay 17 Agust - TGR - S\n18. Irsyad Sahroni - Mounjaro 5mg - pay 17 Agust - TGR - S\n19. Tri Sutrisno - ACIP 2,5MG - PAY 18 AGUS - PU BSD 22 AGUS 2026 - ALMA\n20. RIZKA FAUZIAH - ACIP 2,5MG - PAY 18 AGUS - PU BSD 22 AGUS 2026 - ALMA\n21. Novrida - ACI 0.25mg - Pay 18 Agustus - *Apotek Panel Sumatera* - Liana\n22. Markus Panjaitan - ACI 0.5mg - Pay 18 Agustus - TGR - Siska\n23. Arvina Noviawati - Wegovy 1mg - pay 18 Agust - *PANEL BINJAI* - S\n24. Neny Triana - Wegovy 0.5mg - pay 18 Agust - DPK - S\n25. Wilson Pascall - Mounjaro 2.5mg - pay 18 Agust - TGR - S\n26. Fatimah Alhabsyi - Wegovy 1.7mg - *panel bali ongkir 100k* - pay 19 ag - O\n27. MARIA ULFA - ACIP 2,5MG - PAY 19 AGUS - PU BSD - ALMA\n28. FERDINAND WIJAYA - ACIP 2,5MG - PAY 19 AGUS - PU BSD - ALMA\n29. Siham marwan - wego 0.25mg - bogor tengah - pay 19 agus - U\n30. Kodir - wego 0.25mg - tangsel - pay 19 agus - U\n\nPIC Nana', 0, '2026-08-19 03:56:36', '2026-08-19 03:56:36'),
(2, 1, '255580937679063', 'Assalamualaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai karpet masjid', 0, '2026-08-19 03:57:19', '2026-08-19 03:57:19'),
(3, 2, '274577343160505', 'Assalamualaikum wr wb, Saya mendapatkan informasi dari website nabata karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-20 03:40:38', '2026-08-20 03:40:38'),
(4, 3, '213215178940557', 'mba', 0, '2026-08-20 03:52:23', '2026-08-20 03:52:23'),
(5, 3, '213215178940557', 'aku mau coba hubungka nomer ke akun iklan', 0, '2026-08-20 03:52:26', '2026-08-20 03:52:26'),
(6, 3, '213215178940557', 'tolong dibantu OTP ya', 0, '2026-08-20 03:52:30', '2026-08-20 03:52:30'),
(7, 3, '213215178940557', 'ehh sudah ya ternyata hehe', 0, '2026-08-20 03:54:03', '2026-08-20 03:54:03'),
(8, 3, '213215178940557', 'Nahh itu', 0, '2026-08-20 04:47:06', '2026-08-20 04:47:06'),
(9, 3, '213215178940557', 'Bantar aku tanya tim sosmed', 0, '2026-08-20 04:47:13', '2026-08-20 04:47:13'),
(10, 4, '234179300167691', 'hallo', 0, '2026-08-21 05:41:10', '2026-08-21 05:41:10'),
(11, 4, '234179300167691', 'p', 0, '2026-08-21 05:42:21', '2026-08-21 05:42:21'),
(12, 4, '234179300167691', 'g', 0, '2026-08-21 05:43:20', '2026-08-21 05:43:20'),
(13, 4, '234179300167691', 'hallo', 0, '2026-08-21 05:43:22', '2026-08-21 05:43:22'),
(14, 4, '234179300167691', 'hallo', 0, '2026-08-21 06:59:14', '2026-08-21 06:59:14'),
(15, 4, '234179300167691', 'p', 0, '2026-08-21 07:05:37', '2026-08-21 07:05:37'),
(16, 4, '234179300167691', 'dd', 0, '2026-08-21 07:06:26', '2026-08-21 07:06:26'),
(17, 5, '6287871976694', 'p', 0, '2026-08-21 07:08:03', '2026-08-21 07:08:03'),
(18, 6, '78971597271125', 'You’ve just created a messaging ad in Ads Manager. Turn on your AI agent that responds to chats 24/7. Here are some benefits:\n\n✅ Never miss a chat—your AI responds the way you do and keeps people engaged, saving you time and effort.\n\n🕹️ Stay in control—you can manage your AI and take over chats anytime.', 0, '2026-08-21 09:43:35', '2026-08-21 09:43:35'),
(19, 3, '213215178940557', 'mba manda', 0, '2026-08-21 16:24:21', '2026-08-21 16:24:21'),
(20, 3, '213215178940557', 'iklan nabata sudha aku jadwlkan pagi ini', 0, '2026-08-21 16:24:29', '2026-08-21 16:24:29'),
(21, 3, '213215178940557', 'tolong WA di konekan lagi ke aplikasi CRM ya', 0, '2026-08-21 16:24:41', '2026-08-21 16:24:41'),
(22, 3, '213215178940557', 'https://drive.google.com/drive/folders/1uj2NcKth2mbjuK32TtKBCdlX2CdkZJoV?usp=drive_link\ndisini ya kak revisinya no telp + vo baru \ncc: @255460494032997', 0, '2026-08-21 17:14:24', '2026-08-21 17:14:24'),
(23, 3, '213215178940557', 'https://drive.google.com/drive/folders/1dlE0k5nPeLCqae1kefAQucfmgYeQeDyz', 0, '2026-08-21 17:15:20', '2026-08-21 17:15:20'),
(24, 3, '213215178940557', 'Berikut keempat masjid di lingkungan ponpes Riyadhishshalihin :', 0, '2026-08-21 17:17:22', '2026-08-21 17:17:22'),
(25, 7, '30756277633265', 'Berapa permeter nya', 0, '2026-08-22 02:12:10', '2026-08-22 02:12:10'),
(26, 8, '199200650342433', 'Toko di mana ?\nHarga yang 10 mm brp', 0, '2026-08-22 05:02:34', '2026-08-22 05:02:34'),
(27, 8, '199200650342433', 'Brp harga yg tebal 10 mm dan lebar 120 cm', 0, '2026-08-22 17:48:25', '2026-08-22 17:48:25'),
(28, 8, '199200650342433', 'Sy di tangerang', 0, '2026-08-22 17:49:04', '2026-08-22 17:49:04'),
(29, 8, '199200650342433', 'Untuk mushola pa', 0, '2026-08-22 17:50:01', '2026-08-22 17:50:01'),
(30, 9, '57076239847635', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-22 17:57:14', '2026-08-22 17:57:14'),
(31, 9, '57076239847635', 'Msh proses pembangunan', 0, '2026-08-22 17:58:07', '2026-08-22 17:58:07'),
(32, 10, '232843682812131', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-22 22:02:49', '2026-08-22 22:02:49'),
(33, 11, '206149940969544', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-22 23:49:02', '2026-08-22 23:49:02'),
(34, 3, '213215178940557', 'Di backup dulu saja mba', 0, '2026-08-23 00:02:06', '2026-08-23 00:02:06'),
(35, 3, '213215178940557', 'Takut ara eror', 0, '2026-08-23 00:02:10', '2026-08-23 00:02:10'),
(36, 12, '40952915861625', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-23 05:37:59', '2026-08-23 05:37:59'),
(37, 13, '119305668681943', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-23 05:41:45', '2026-08-23 05:41:45'),
(38, 3, '213215178940557', 'Mba perpindahan stage iru keyword nya di mba manda', 0, '2026-08-23 22:08:08', '2026-08-23 22:08:08'),
(39, 3, '213215178940557', 'Bukan customer mba', 0, '2026-08-23 22:08:24', '2026-08-23 22:08:24'),
(40, 3, '213215178940557', 'Disesuaikan dengan trigger keyword yg sudsh dibuat mad ashbail kemarin mba', 0, '2026-08-23 22:09:42', '2026-08-23 22:09:42'),
(41, 3, '213215178940557', 'Otomatis', 0, '2026-08-23 22:19:48', '2026-08-23 22:19:48'),
(42, 3, '213215178940557', 'Kalo mba manda ngetik keyword nya', 0, '2026-08-23 22:20:00', '2026-08-23 22:20:00'),
(43, 3, '213215178940557', 'Dia akan pindah stage mba', 0, '2026-08-23 22:20:05', '2026-08-23 22:20:05'),
(44, 3, '213215178940557', 'Harusnya berubah sih', 0, '2026-08-23 22:21:50', '2026-08-23 22:21:50'),
(45, 14, '245273284575451', 'Halo, Kak. 👋\n\nIngin website lebih optimal di Google dan menjangkau lebih banyak calon pelanggan?\n\nLayanan SEO kami membantu meningkatkan kualitas website agar memiliki peluang tampil lebih baik di hasil pencarian organik. Dengan begitu, website dapat terus memperoleh trafik tanpa selalu mengandalkan iklan berbayar.\n\nSilakan konsultasi gratis apabila berminat.', 0, '2026-08-23 23:47:05', '2026-08-23 23:47:05'),
(46, 10, '232843682812131', 'Lagi cari karpet buat ballrom', 0, '2026-08-24 03:53:08', '2026-08-24 03:53:08'),
(47, 15, '201658009141455', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-24 20:07:41', '2026-08-24 20:07:41'),
(48, 15, '201658009141455', 'Cek harga untuk panjang 7 meter', 0, '2026-08-24 20:08:02', '2026-08-24 20:08:02'),
(49, 15, '201658009141455', 'Musola', 0, '2026-08-24 20:44:17', '2026-08-24 20:44:17'),
(50, 3, '213215178940557', 'Mba', 0, '2026-08-26 01:37:42', '2026-08-26 01:37:42'),
(51, 3, '213215178940557', 'Nomer WA disconnect', 0, '2026-08-26 01:37:50', '2026-08-26 01:37:50'),
(52, 3, '213215178940557', 'Coba dihubugian lagi mba dengan CRM', 0, '2026-08-26 01:38:02', '2026-08-26 01:38:02'),
(53, 16, '144564505579721', 'hallo', 0, '2026-08-28 02:34:34', '2026-08-28 02:34:34'),
(54, 16, '6285772053530', 'mohon maaf, kami tidak dapat melanjutkan percakapan', 1, '2026-08-28 02:34:55', '2026-08-28 02:34:55'),
(55, 17, '6285772053530', 'mohon maaf, kami tidak dapat melanjutkan percakapan', 1, '2026-08-28 02:35:58', '2026-08-28 02:35:58'),
(56, 18, '6285772053530', 'mohon maaf, kami tidak dapat melanjutkan percakapan', 1, '2026-08-28 02:36:10', '2026-08-28 02:36:10'),
(57, 14, '6285772053530', 'mohon maaf, kami tidak dapat melanjutkan percakapan', 1, '2026-08-28 02:36:27', '2026-08-28 02:36:27'),
(58, 19, '6285772053530', 'boleh saya tahu kebutuhan karpet yang dicari seperti apa ?', 1, '2026-08-28 02:41:17', '2026-08-28 02:41:17'),
(59, 8, '6285772053530', 'boleh saya tahu kebutuhan karpet yang dicari seperti apa ?', 1, '2026-08-28 02:42:10', '2026-08-28 02:42:10'),
(60, 8, '6285772053530', 'boleh saya tahu kebutuhan karpet yang dicari seperti apa ?', 1, '2026-08-28 02:42:11', '2026-08-28 02:42:11'),
(61, 3, '6285772053530', 'mohon maaf, kami tidak dapat melanjutkan percakapan', 1, '2026-08-28 02:42:45', '2026-08-28 02:42:45'),
(62, 2, '6285772053530', 'mohon maaf, kami tidak dapat melanjutkan percakapan', 1, '2026-08-28 02:42:54', '2026-08-28 02:42:54'),
(63, 2, '6285772053530', 'TESTING', 1, '2026-08-28 02:42:55', '2026-08-28 02:42:55'),
(64, 20, '6285772053530', '085748276427', 1, '2026-08-28 02:45:00', '2026-08-28 02:45:00'),
(65, 1, '6285772053530', 'mohon maaf, kami tidak dapat melanjutkan percakapan', 1, '2026-08-28 02:45:25', '2026-08-28 02:45:25'),
(66, 2, '274577343160505', 'aman mba?', 0, '2026-08-28 02:53:03', '2026-08-28 02:53:03'),
(67, 2, '6285772053530', 'Aman pak, sudah bisa', 1, '2026-08-28 02:55:27', '2026-08-28 02:55:27'),
(68, 2, '6285772053530', 'Aman pak, sudah bisa', 1, '2026-08-28 02:55:27', '2026-08-28 02:55:27'),
(69, 21, '203036106457112', 'Klo obras karpet berapa biayanya ya per meter', 0, '2026-08-28 03:51:35', '2026-08-28 03:51:35'),
(70, 21, '6285772053530', 'Halo pak, Boleh diinformasikan pemasangan karpetnya berada di daerah mana ya?\n\nApabila lokasi berada di area Jabodetabek, Nabata Karpet menyediakan layanan survei lokasi GRATIS. Tim kami akan datang langsung untuk:\n✅ Melakukan pengecekan dan pengukuran area.\n✅ Membawa contoh sampel karpet sehingga Bapak/Ibu dapat melihat langsung warna, motif, dan kualitasnya.\n✅ Memberikan rekomendasi yang paling sesuai dengan kebutuhan ruangan.\n\nLayanan survei ini 100% gratis, tidak mengikat, dan tanpa kewajiban melakukan pembelian, sehingga Bapak/Ibu dapat berkonsultasi terlebih dahulu dengan lebih nyaman. 😊', 1, '2026-08-28 03:52:38', '2026-08-28 03:52:38'),
(71, 21, '203036106457112', 'Cengkareng Jakarta Barat', 0, '2026-08-28 03:55:54', '2026-08-28 03:55:54'),
(72, 21, '6285772053530', 'Baik pak/Bu, boleh kami tahu rencana berapa meter ya karpet yang akan di obras ?', 1, '2026-08-28 03:56:37', '2026-08-28 03:56:37'),
(73, 22, '6285772053530', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data berikut ya Kak/Bapak/Ibu:\n\nNama:\nDomisili:\nKebutuhan: (Karpet Masjid / Interior / Custom)\nPerkiraan Luas Area:\n\nTerima kasih!', 1, '2026-08-28 04:00:06', '2026-08-28 04:00:06'),
(74, 22, '48438674739434', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-28 04:00:11', '2026-08-28 04:00:11'),
(75, 22, '48438674739434', 'Hg m2 berpa per roll hg nya', 0, '2026-08-28 04:09:49', '2026-08-28 04:09:49'),
(76, 22, '6285772053530', 'Mohon izin bertanya, boleh dibantu informasi ukuran masjid yang akan dipasang nanti :\n\n- Panjang ruangan (dari depan ke belakang) berapa meter?\n- Lebar ruangan (dari dinding kiri ke dinding kanan) berapa meter?\n\nApabila terdapat pilar, lekukan, atau bentuk ruangan yang tidak simetris, mohon diinformasikan juga ya.\n\nJika berkenan, mohon dibantu kirim foto atau denah ruangan. \nTim kami akan membantu menghitung kebutuhan karpet dan harga agar sesuai dengan kondisi ruangan 😊', 1, '2026-08-28 04:32:34', '2026-08-28 04:32:34'),
(77, 22, '6285772053530', 'Mohon izin bertanya, boleh dibantu informasi ukuran masjid yang akan dipasang nanti :\n\n- Panjang ruangan (dari depan ke belakang) berapa meter?\n- Lebar ruangan (dari dinding kiri ke dinding kanan) berapa meter?\n\nApabila terdapat pilar, lekukan, atau bentuk ruangan yang tidak simetris, mohon diinformasikan juga ya.\n\nJika berkenan, mohon dibantu kirim foto atau denah ruangan. \nTim kami akan membantu menghitung kebutuhan karpet dan harga agar sesuai dengan kondisi ruangan 😊', 1, '2026-08-28 04:32:35', '2026-08-28 04:32:35'),
(78, 23, '253566329585826', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-28 04:52:05', '2026-08-28 04:52:05'),
(79, 23, '6285772053530', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data berikut ya Kak/Bapak/Ibu:\n\nNama:\nDomisili:\nKebutuhan: (Karpet Masjid / Interior / Custom)\nPerkiraan Luas Area:\n\nTerima kasih!', 1, '2026-08-28 04:52:07', '2026-08-28 04:52:07'),
(80, 21, '203036106457112', 'Panjang 17 m di kali 7 karpet ka', 0, '2026-08-28 05:08:19', '2026-08-28 05:08:19'),
(81, 21, '6285772053530', 'Baik pak/Bu, mohon ditunggu kami konfirmasi ke tim ya 🙏\nKalo boleh kami tahu,nama masjidnya apa, kemudian lokasi detail nya dimana ya?', 1, '2026-08-28 05:10:54', '2026-08-28 05:10:54'),
(82, 24, '97788385845368', 'Malam', 0, '2026-08-28 05:20:43', '2026-08-28 05:20:43'),
(83, 21, '203036106457112', 'Masjid Ar-rahmah lokasi di Cengkareng Timur Jakarta Barat', 0, '2026-08-28 05:45:27', '2026-08-28 05:45:27'),
(84, 24, '6285772053530', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data berikut ya Kak/Bapak/Ibu:\n\nNama:\nDomisili:\nKebutuhan: (Karpet Masjid / Interior / Custom)\nPerkiraan Luas Area:\n\nTerima kasih!', 1, '2026-08-28 06:18:52', '2026-08-28 06:18:52'),
(85, 21, '6285772053530', 'Baik , kalo boleh tahu dengan bapak/ ibu siapa ya ?', 1, '2026-08-28 06:19:37', '2026-08-28 06:19:37'),
(86, 21, '6285772053530', 'Selamat pagi Pak/Bu, ijin konfirmasi ulang untuk biaya obras di alamat berikut 35 ribu per meter. \nRencana dijadwalkan kapan ya pemotongan karpet Masjid nya ?', 1, '2026-08-28 16:31:25', '2026-08-28 16:31:25'),
(87, 24, '6285772053530', 'Halo pak, Boleh diinformasikan pemasangan karpetnya berada di daerah mana ya?\n\nApabila lokasi berada di area Jabodetabek, Nabata Karpet menyediakan layanan survei lokasi GRATIS. Tim kami akan datang langsung untuk:\n✅ Melakukan pengecekan dan pengukuran area.\n✅ Membawa contoh sampel karpet sehingga Bapak/Ibu dapat melihat langsung warna, motif, dan kualitasnya.\n✅ Memberikan rekomendasi yang paling sesuai dengan kebutuhan ruangan.\n\nLayanan survei ini 100% gratis, tidak mengikat, dan tanpa kewajiban melakukan pembelian, sehingga Bapak/Ibu dapat berkonsultasi terlebih dahulu dengan lebih nyaman. 😊', 1, '2026-08-28 16:31:41', '2026-08-28 16:31:41'),
(88, 21, '203036106457112', 'Ini karpet lama yg pada bodol', 0, '2026-08-28 16:44:32', '2026-08-28 16:44:32'),
(89, 21, '6285772053530', 'Baik pak, kami konfirmasi ke tim ya agar dilakukan penjadwalan kesana 🙏\n\nApakah berkenan ganti karpetnya sekalian pak?\nKami siap membawa contoh untuk dicek saat survei lokasi nanti', 1, '2026-08-28 16:46:41', '2026-08-28 16:46:41'),
(90, 21, '6285772053530', 'Jalan Rusun BCI Raya, RT.7/RW.16, Kelurahan Cengkareng Timur, Kecamatan Cengkareng, Kota Jakarta Barat.\n\nApakah lokasi detail nya sudah sesuai pak ?', 1, '2026-08-28 16:48:23', '2026-08-28 16:48:23'),
(91, 21, '203036106457112', 'Nanti dulu pak, saya bituh persetujuan dr pengurus karena terkait budhetnya', 0, '2026-08-28 16:49:27', '2026-08-28 16:49:27'),
(92, 21, '6285772053530', 'Baik pak, apabila berkenan Nabata Karpet menyediakan layanan survei lokasi GRATIS. Tim kami akan datang langsung untuk:\n✅ Melakukan pengecekan dan pengukuran area.\n✅ Membawa contoh sampel karpet sehingga Bapak/Ibu dapat melihat langsung warna, motif, dan kualitasnya.\n✅ Memberikan rekomendasi yang paling sesuai dengan kebutuhan ruangan.\n\nLayanan survei ini 100% gratis, tidak mengikat, dan tanpa kewajiban melakukan pembelian, sehingga Bapak/Ibu dapat berkonsultasi terlebih dahulu dengan lebih nyaman. 😊', 1, '2026-08-28 16:53:36', '2026-08-28 16:53:36'),
(93, 21, '203036106457112', 'Note 👌', 0, '2026-08-28 16:53:55', '2026-08-28 16:53:55'),
(94, 21, '6285772053530', 'Terimakasih pak, kami tunggu konfirmasinya. Tim kami siap datang bersilaturahmi kesana😊', 1, '2026-08-28 16:55:02', '2026-08-28 16:55:02'),
(95, 23, '6285772053530', 'Halo pak, Boleh diinformasikan pemasangan karpetnya berada di daerah mana ya?\n\nApabila lokasi berada di area Jabodetabek, Nabata Karpet menyediakan layanan survei lokasi GRATIS. Tim kami akan datang langsung untuk:\n✅ Melakukan pengecekan dan pengukuran area.\n✅ Membawa contoh sampel karpet sehingga Bapak/Ibu dapat melihat langsung warna, motif, dan kualitasnya.\n✅ Memberikan rekomendasi yang paling sesuai dengan kebutuhan ruangan.\n\nLayanan survei ini 100% gratis, tidak mengikat, dan tanpa kewajiban melakukan pembelian, sehingga Bapak/Ibu dapat berkonsultasi terlebih dahulu dengan lebih nyaman. 😊', 1, '2026-08-28 17:45:28', '2026-08-28 17:45:28'),
(96, 22, '6285772053530', 'Halo pak, Boleh diinformasikan pemasangan karpetnya berada di daerah mana ya?\n\nApabila lokasi berada di area Jabodetabek, Nabata Karpet menyediakan layanan survei lokasi GRATIS. Tim kami akan datang langsung untuk:\n✅ Melakukan pengecekan dan pengukuran area.\n✅ Membawa contoh sampel karpet sehingga Bapak/Ibu dapat melihat langsung warna, motif, dan kualitasnya.\n✅ Memberikan rekomendasi yang paling sesuai dengan kebutuhan ruangan.\n\nLayanan survei ini 100% gratis, tidak mengikat, dan tanpa kewajiban melakukan pembelian, sehingga Bapak/Ibu dapat berkonsultasi terlebih dahulu dengan lebih nyaman. 😊', 1, '2026-08-28 17:45:53', '2026-08-28 17:45:53'),
(97, 13, '6285772053530', 'Halo Pak/Bu, semoga sehat dan aktivitasnya lancar ya. 😊\n\nKami izin follow up sebentar terkait kebutuhan karpetnya. Mungkin beberapa waktu terakhir Kakak sedang sibuk, jadi belum sempat membalas chat kami.\n\nJika nanti sudah siap atau ada yang ingin ditanyakan, cukup balas pesan ini ya. Kami dengan senang hati akan membantu.\n\nTerima kasih banyak. Semoga harinya menyenangkan. 🙏', 1, '2026-08-28 17:46:54', '2026-08-28 17:46:54'),
(98, 11, '6285772053530', 'Halo Pak/Bu, semoga sehat dan aktivitasnya lancar ya. 😊\n\nKami izin follow up sebentar terkait kebutuhan karpetnya. Mungkin beberapa waktu terakhir Kakak sedang sibuk, jadi belum sempat membalas chat kami.\n\nJika nanti sudah siap atau ada yang ingin ditanyakan, cukup balas pesan ini ya. Kami dengan senang hati akan membantu.\n\nTerima kasih banyak. Semoga harinya menyenangkan. 🙏', 1, '2026-08-28 17:47:26', '2026-08-28 17:47:26'),
(99, 25, '66194220572703', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-29 08:25:53', '2026-08-29 08:25:53'),
(100, 25, '6285772053530', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data ringkas berikut ya Kak/Bapak/Ibu:\n\nNama:\nDomisis:\nKebutuhan: (Karpet Masjid / Interior / Custom)\nPerkiraan Luas Area:\n\nTerima kasih!', 1, '2026-08-29 08:28:30', '2026-08-29 08:28:30'),
(101, 26, '12416800800896', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-29 16:33:52', '2026-08-29 16:33:52'),
(102, 26, '6285772053530', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data ringkas berikut ya Kak/Bapak/Ibu:\n\nNama:\nDomisis:\nKebutuhan: (Karpet Masjid / Interior / Custom)\nPerkiraan Luas Area:\n\nTerima kasih!', 1, '2026-08-29 16:33:59', '2026-08-29 16:33:59'),
(103, 26, '6285772053530', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data ringkas berikut ya Kak/Bapak/Ibu:\n\nNama:\nDomisis:\nKebutuhan: (Karpet Masjid / Interior / Custom)\nPerkiraan Luas Area:\n\nTerima kasih!', 1, '2026-08-29 16:33:59', '2026-08-29 16:33:59'),
(104, 26, '12416800800896', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data ringkas berikut ya Kak/Bapak/Ibu:\n\nNama: Ust Ayi ZM\nDomisis: PONPES BAROKATUL KAMILAH, Citatah Cipatat\nKebutuhan: (Karpet Masjid )\nPerkiraan Luas Area: 63m persegi\n\nTerima kasih!', 0, '2026-08-29 16:37:49', '2026-08-29 16:37:49'),
(105, 26, '6285772053530', 'boleh saya tahu kebutuhan , agar kami dapat memberikan rekomendasi yang paling sesuai :\n\nApakah Bapak sedang mencari karpet shafroll yang siap pakai, atau karpet custom yang lebih spesifik sesuai kebutuhan ?', 1, '2026-08-29 16:40:10', '2026-08-29 16:40:10'),
(106, 26, '12416800800896', 'Maaf karpet custom itu seperti apa ya', 0, '2026-08-29 16:43:40', '2026-08-29 16:43:40'),
(107, 26, '6285772053530', 'Karpet custom adalah karpet yang dirancang dan diproduksi khusus berdasarkan permintaan pemesan, mulai dari ukuran, bentuk, motif, warna, ketebalan, desain, hingga jenis bahan yang digunakan ya pak Ustadz 😊', 1, '2026-08-29 17:03:52', '2026-08-29 17:03:52'),
(108, 26, '6285772053530', 'Nabata Karpet menyediakan layanan survei lokasi GRATIS. Tim kami akan datang langsung untuk:\n✅ Melakukan pengecekan dan pengukuran area.\n✅ Membawa contoh sampel karpet sehingga Bapak/Ibu dapat melihat langsung warna, motif, dan kualitasnya.\n✅ Memberikan rekomendasi yang paling sesuai dengan kebutuhan ruangan.\n\nLayanan survei ini 100% gratis, tidak mengikat, dan tanpa kewajiban melakukan pembelian, sehingga Bapak/Ibu dapat berkonsultasi terlebih dahulu dengan lebih nyaman. 😊\n\nApakah bapak berkenan dijadwalkan? Untuk area Jabodetabek survei nya gratis ya pak', 1, '2026-08-29 17:04:44', '2026-08-29 17:04:44'),
(109, 26, '12416800800896', 'Kalau perbedaan harganya antara yg shaff roll dan custom tinggi mana?', 0, '2026-08-29 17:05:09', '2026-08-29 17:05:09'),
(110, 26, '6285772053530', 'Tinggi yang custom Pak ustadz karena dibuat khusus dan lebih detail desainnya 😊', 1, '2026-08-29 17:05:50', '2026-08-29 17:05:50'),
(111, 26, '12416800800896', 'O iya siap nanti aja penjadwalan nya... Cuma mau tahu budget biasanya untuk ukuran tersebut kisaran berapa biar kami bisa kumpul kumpul dulu anggaran nya.🙏🏻🙏🏻', 0, '2026-08-29 17:06:50', '2026-08-29 17:06:50'),
(112, 26, '6285772053530', 'bantu cek estimasi harganya ya pak Ustadz , secepatnya akan kami hubungi kembali🙏😊', 1, '2026-08-29 17:10:15', '2026-08-29 17:10:15'),
(113, 25, '6285772053530', 'Halo pak, Boleh diinformasikan pemasangan karpetnya berada di daerah mana ya?\n\nApabila lokasi berada di area Jabodetabek, Nabata Karpet menyediakan layanan survei lokasi GRATIS. Tim kami akan datang langsung untuk:\n✅ Melakukan pengecekan dan pengukuran area.\n✅ Membawa contoh sampel karpet sehingga Bapak/Ibu dapat melihat langsung warna, motif, dan kualitasnya.\n✅ Memberikan rekomendasi yang paling sesuai dengan kebutuhan ruangan.\n\nLayanan survei ini 100% gratis, tidak mengikat, dan tanpa kewajiban melakukan pembelian, sehingga Bapak/Ibu dapat berkonsultasi terlebih dahulu dengan lebih nyaman. 😊', 1, '2026-08-29 17:14:24', '2026-08-29 17:14:24'),
(114, 25, '66194220572703', 'Ini contohnya ada', 0, '2026-08-29 17:15:12', '2026-08-29 17:15:12'),
(115, 25, '6285772053530', 'Ready pak 😊', 1, '2026-08-29 17:15:20', '2026-08-29 17:15:20'),
(116, 25, '6285772053530', 'Mohon izin bertanya, boleh dibantu informasi ukuran masjid yang akan dipasang nanti :\n\n- Panjang ruangan (dari depan ke belakang) berapa meter?\n- Lebar ruangan (dari dinding kiri ke dinding kanan) berapa meter?\n\nApabila terdapat pilar, lekukan, atau bentuk ruangan yang tidak simetris, mohon diinformasikan juga ya.\n\nJika berkenan, mohon dibantu kirim foto atau denah ruangan. \nTim kami akan membantu menghitung kebutuhan karpet agar sesuai dengan kondisi ruangan 😊', 1, '2026-08-29 17:15:25', '2026-08-29 17:15:25'),
(117, 26, '6285772053530', 'Selamat siang pak Ustadz, boleh dibantu untuk kebutuhan Karpetnya mau yang kwalitas standar, menengah atau premium ya ?', 1, '2026-08-29 22:09:58', '2026-08-29 22:09:58'),
(118, 26, '6285772053530', 'boleh dibantu informasi ukuran masjid yang akan dipasang nanti :\n\n- Panjang ruangan (dari depan ke belakang) berapa meter?\n- Lebar ruangan (dari dinding kiri ke dinding kanan) berapa meter?\n\nApabila terdapat pilar, lekukan, atau bentuk ruangan yang tidak simetris, mohon diinformasikan juga ya.\n\nJika berkenan, mohon dibantu kirim foto atau denah ruangan. \nTim kami akan membantu menghitung kebutuhan karpet agar sesuai dengan kondisi ruangan 😊', 1, '2026-08-29 22:10:11', '2026-08-29 22:10:11'),
(119, 26, '12416800800896', 'O iya mohon maaf saya sedang di luar🙏🏻🙏🏻', 0, '2026-08-29 22:10:42', '2026-08-29 22:10:42'),
(120, 26, '6285772053530', 'Baik pak Ustadz, jika sudah luang bisa dilanjutkan kembali ya\n\nTerimakasih 😊🙏', 1, '2026-08-29 22:13:06', '2026-08-29 22:13:06'),
(121, 26, '12416800800896', '👍', 0, '2026-08-29 22:17:40', '2026-08-29 22:17:40'),
(122, 25, '6285772053530', 'Assalamualaikum, Bapak / Ibu, izin follow up ya\nBerikut kami kirimkan contoh sampel/katalog karpet Nabata sebagai referensi. Silahkan dilihat terlebih dahulu.\n\nApabila sudah ada pilihan, silakan informasikan kepada kami. Dengan senang hati kami akan membantu memberikan informasi detail produk\n\nTerima kasih 😊🙏', 1, '2026-08-30 17:30:25', '2026-08-30 17:30:25'),
(123, 21, '6285772053530', 'Assalamualaikum Bapak / Ibu, semoga sehat selalu\nBerikut kami lampirkan beberapa testimoni dari pelanggan yang telah menggunakan karpet Nabata sebagai referensi.\n\nSemoga dapat membantu memberikan gambaran mengenai kualitas produk dan pelayanan kami. Apabila Bapak/Ibu memiliki pertanyaan atau ingin berkonsultasi mengenai jenis karpet yang sesuai dengan kebutuhan, jangan ragu untuk menghubungi kami.\n\nTerima kasih 😊', 1, '2026-08-30 17:31:24', '2026-08-30 17:31:24'),
(124, 21, '203036106457112', 'Saya sdh konfirmasi ke Pak DKM, kpn2 akan dilaksanakan survey ke lokasi ?', 0, '2026-08-30 17:33:11', '2026-08-30 17:33:11'),
(125, 21, '6285772053530', 'Baik pak, kami tunggu konfirmasinya 😊', 1, '2026-08-30 17:35:00', '2026-08-30 17:35:00'),
(126, 21, '203036106457112', 'Dalam minggu ini apakah bisa?', 0, '2026-08-30 17:42:44', '2026-08-30 17:42:44'),
(127, 21, '6285772053530', 'Baik pak. Kami konfirmasi ke tim dulu ya. Boleh kami tahu dengan bapak siapa saya berbicara?', 1, '2026-08-30 17:46:52', '2026-08-30 17:46:52'),
(128, 21, '203036106457112', 'Dengan Den Bagoes 🙏', 0, '2026-08-30 18:30:20', '2026-08-30 18:30:20'),
(129, 21, '6285772053530', 'alamat lokasinya sudah kami catat ya Pak : \nMasjid Ar-rahmah, kelurahan Cengkareng Timur, Jln Rusun BCI Raya RT 07 RW 16, Kecamatan Cengkareng, Jakarta Barat\nPenanggung jawab : Den Bagoes\nNo wa : +62 852-2598-8191\nKebutuhan survei : obras karpet', 1, '2026-08-30 18:48:15', '2026-08-30 18:48:15'),
(130, 21, '6285772053530', 'Apakah perlu kami bawakan contoh sampel karpet untuk ganti karpetnya sekalian pak ?', 1, '2026-08-30 18:48:35', '2026-08-30 18:48:35'),
(131, 21, '203036106457112', 'Gak apa-apa dibawakan saja siapa tahu nanti DKM nya tertarik, oya penanggung jawab nya selain saya pak DKM Kodirin juga boleh', 0, '2026-08-30 18:50:30', '2026-08-30 18:50:30'),
(132, 21, '6285772053530', 'Baik pak. Untuk koordinasi di nomor ini saja ya pak ?', 1, '2026-08-30 18:51:24', '2026-08-30 18:51:24'),
(133, 21, '203036106457112', 'Boleh', 0, '2026-08-30 18:51:41', '2026-08-30 18:51:41'),
(134, 21, '6285772053530', 'Kami konfirmasi ke tim dulu ya untuk penjadwalan 🙏', 1, '2026-08-30 18:51:43', '2026-08-30 18:51:43'),
(135, 21, '6285772053530', 'Halo Pak, apakah bisa diagendakan besok pagi ya ? \nMohon dibantu sharelokc juga ya pak 🙏', 1, '2026-08-30 19:09:34', '2026-08-30 19:09:34'),
(136, 21, '203036106457112', 'Sebentar saya tak konfirmasi sama DKMnya krn klo di jam kerja saya gak bisa', 0, '2026-08-30 19:11:14', '2026-08-30 19:11:14'),
(137, 21, '6285772053530', 'Baik pak. Terimakasih 🙏', 1, '2026-08-30 19:14:42', '2026-08-30 19:14:42'),
(138, 21, '203036106457112', 'Boleh besok pagi ditunggu pak DKM jam berapa ya?', 0, '2026-08-30 19:29:12', '2026-08-30 19:29:12'),
(139, 21, '6285772053530', 'Baik pak, sebentar kami konfirmasi ke tim ya 😊🙏', 1, '2026-08-30 19:30:29', '2026-08-30 19:30:29'),
(140, 21, '203036106457112', 'Oke', 0, '2026-08-30 19:30:43', '2026-08-30 19:30:43'),
(141, 21, '6285772053530', 'Selamat siang pak, besok kemungkinan tim kami sampai disana pukul 10 paling cepat, nanti sekalian solat dhuhur disana ya pak 😊🙏', 1, '2026-08-30 20:42:32', '2026-08-30 20:42:32'),
(142, 27, '17627284004962', 'Halo Admin Nabata Karpet, Saya ingin tanya katalog harga karpet di Nabata.', 0, '2026-08-30 23:07:41', '2026-08-30 23:07:41'),
(143, 27, '6285772053530', 'Assalamu’alaikum, Selamat datang di Nabata Karpet! 🕌✨\nTerima kasih telah menghubungi kami.\n\nAgar kami bisa memberikan rekomendasi katalog produk, boleh bantu isi data berikut ya Kak/Bapak/Ibu:\n\nNama:\nDomisili:\nKebutuhan: (Karpet Masjid / Interior / Custom)\nPerkiraan Luas Area:\n\nTerima kasih!', 1, '2026-08-30 23:07:42', '2026-08-30 23:07:42'),
(144, 21, '203036106457112', 'Baik terimakasih 🙏', 0, '2026-08-30 23:41:36', '2026-08-30 23:41:36'),
(145, 21, '6285772053530', 'Sama sama pak, besok untuk koordinasi apakah bisa kami hubungi nomor ini pak ?', 1, '2026-08-30 23:43:56', '2026-08-30 23:43:56'),
(146, 21, '203036106457112', 'Bisa, atau langsung nomer DKM juga boleh', 0, '2026-08-30 23:45:08', '2026-08-30 23:45:08'),
(147, 21, '6285772053530', 'Untuk nomor DKM nya boleh dibantu infokan pak? 🙏', 1, '2026-08-30 23:46:20', '2026-08-30 23:46:20'),
(148, 21, '6285772053530', 'Baik pak, terimakasih banyak', 1, '2026-08-30 23:46:57', '2026-08-30 23:46:57'),
(149, 28, '16479923740792', 'Selamat siang, perkenalkan saya Annaz dari PT Wika Gedung', 0, '2026-09-01 02:28:41', '2026-09-01 02:28:41'),
(150, 28, '6285746338899', 'Thank you for contacting Marketing WKM! Please let us know how we can help you.', 1, '2026-09-01 02:28:43', '2026-09-01 02:28:43'),
(151, 28, '16479923740792', 'Pak kami sedang ada tender proyek Jasa Konstruksi Rehab Total Gedung Sekolah Tahun 2026 Paket 2, yang berlokasi di :\n- Gedung SMKN 36 Jakarta, Cilincing, Jakarta Utara. (Maps: https://maps.app.goo.gl/z5zyKactesHiV9PR7)\n- USB SDN Kelurahan Koja, Koja, Jakarta Utara. (Maps: https://maps.app.goo.gl/SWwhixFRZ1MhLQE46 )\nApa bisa dibantu penawaran harga pintu kayu 🙏🏻', 0, '2026-09-01 02:29:26', '2026-09-01 02:29:26'),
(152, 29, '90298164224038', 'Hello', 0, '2026-09-01 04:19:23', '2026-09-01 04:19:23'),
(153, 29, '6285746338899', 'Terimakasih telah menghubungi kami.Saat ini kami sedang offline.Jam operasional kami adalah Senin-Jumat pukul 08.00-16.30.Pesan Anda akan kami balas segera setelah kami aktif kembali', 1, '2026-09-01 04:19:26', '2026-09-01 04:19:26'),
(154, 29, '6285746338899', 'Thank you for contacting Marketing WKM! Please let us know how we can help you.', 1, '2026-09-01 04:19:26', '2026-09-01 04:19:26'),
(155, 29, '90298164224038', 'Im imane from AH AFRICA', 0, '2026-09-01 04:19:49', '2026-09-01 04:19:49'),
(156, 28, '6285746338899', 'Malam pak', 1, '2026-09-01 04:46:51', '2026-09-01 04:46:51'),
(157, 28, '6285746338899', 'Bisa pak', 1, '2026-09-01 04:46:53', '2026-09-01 04:46:53'),
(158, 28, '6285746338899', 'Daun pintu saja kah pak?', 1, '2026-09-01 04:47:11', '2026-09-01 04:47:11'),
(159, 28, '6285746338899', 'Kami juga ada upvc', 1, '2026-09-01 04:47:25', '2026-09-01 04:47:25'),
(160, 29, '6285746338899', 'Hello', 1, '2026-09-01 04:47:44', '2026-09-01 04:47:44'),
(161, 30, '34789386113092', 'halo, ini yg custom pintu aluminum ya', 0, '2026-09-01 05:22:50', '2026-09-01 05:22:50'),
(162, 30, '6285746338899', 'Thank you for contacting Marketing WKM! Please let us know how we can help you.', 1, '2026-09-01 05:22:52', '2026-09-01 05:22:52'),
(163, 30, '6285746338899', 'Terimakasih telah menghubungi kami.Saat ini kami sedang offline.Jam operasional kami adalah Senin-Jumat pukul 08.00-16.30.Pesan Anda akan kami balas segera setelah kami aktif kembali', 1, '2026-09-01 05:22:52', '2026-09-01 05:22:52'),
(164, 29, '90298164224038', 'Dear Sir/Madam,\n\nWe are AH AFRICA, the West African subsidiary of ALH Real Estate, a Moroccan real estate group developing residential projects across several countries in West Africa.\n\nAs part of our ongoing projects in the region, we are currently sourcing interior doors for Bô Résidence, an economy residential development located in Abidjan, Côte d’Ivoire, comprising nearly 900 apartments.\n\nWe are looking to identify reliable and competitive manufacturers with the production capacity and experience required to support this project.\n\nAs this is an economy residential project, competitive pricing and a good quality-to-price ratio are key considerations for us, while maintaining the required level of quality and reliability.\n\nPlease find attached the door schedule, quantities and execution drawings. We kindly ask you to review the documents and provide us with your best quotation based on the required specifications, or propose suitable equivalent options from your range.\n\nPlease include the following information in your offer:\n\nProposed models, materials and finishes\nUnit prices and total amount\nDoor handles and hardware options, with silver finish required\nProduction capacity\nProduction lead time\nMinimum order quantity, if applicable\nPayment terms\nIncoterm and delivery terms\nExport experience and capacity to Côte d’Ivoire / West Africa\n\nThe order is planned to be delivered in two batches according to the project schedule. Please confirm your ability to accommodate this production and delivery arrangement.\n\nAs this sourcing process is time-sensitive, we would appreciate receiving your quotation at your earliest convenience.\n\nWe look forward to receiving your offer and to the possibility of establishing a long-term cooperation with your company.', 0, '2026-09-01 08:19:11', '2026-09-01 08:19:11'),
(165, 31, '6285746338899', 'Weh gua pengen jd ba dah', 1, '2026-09-01 09:24:40', '2026-09-01 09:24:40'),
(166, 31, '38594827821120', 'ba apaan si', 0, '2026-09-01 09:24:48', '2026-09-01 09:24:48'),
(167, 31, '6285746338899', 'eeeee apa gt', 1, '2026-09-01 09:24:59', '2026-09-01 09:24:59'),
(168, 31, '6285746338899', 'sabun', 1, '2026-09-01 09:25:06', '2026-09-01 09:25:06'),
(169, 31, '38594827821120', 'jangan co, ntar lu di rekrut jd dakinya', 0, '2026-09-01 09:25:35', '2026-09-01 09:25:35');

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
(4, '2026_08_06_061606_create_personal_access_tokens_table', 1),
(5, '2026_08_06_061618_create_leads_table', 1),
(6, '2026_08_06_101242_add_notes_and_priority_to_leads_table', 1),
(7, '2026_08_10_000001_create_wa_accounts_table', 1),
(8, '2026_08_10_000002_add_wa_account_id_to_leads_table', 1),
(9, '2026_08_10_000003_create_lead_messages_table', 1),
(10, '2026_08_10_000004_add_role_status_and_wa_account_id_to_users_table', 1),
(11, '2026_08_11_000001_create_pipeline_stages_table', 1),
(12, '2026_08_11_000002_create_stage_triggers_table', 1),
(13, '2026_08_11_000003_add_is_default_to_pipeline_stages_table', 1),
(14, '2026_08_12_125107_add_disconnect_email_settings_to_wa_accounts_table', 1),
(15, '2026_08_12_202500_add_brand_approval_fields', 1),
(16, '2026_08_12_210000_create_smtp_settings_table', 1),
(17, '2026_08_12_230000_add_assigned_to_to_leads_table', 1),
(18, '2026_08_21_120958_add_ai_suggestion_columns_to_leads_table', 2),
(19, '2026_08_22_000001_create_ai_lead_comparisons_table', 2),
(20, '2026_08_22_000002_add_ai_concluded_stage_to_leads_table', 2),
(21, '2026_08_22_000003_add_wa_session_columns_to_users_table', 2),
(22, '2026_08_29_000001_create_brand_supervisors_table', 3),
(23, '2026_09_01_000001_add_disconnect_alert_emails_to_settings', 4);

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

-- --------------------------------------------------------

--
-- Table structure for table `pipeline_stages`
--

CREATE TABLE `pipeline_stages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wa_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL DEFAULT 1,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `color` varchar(255) NOT NULL DEFAULT 'purple',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pipeline_stages`
--

INSERT INTO `pipeline_stages` (`id`, `wa_account_id`, `name`, `order`, `is_default`, `color`, `created_at`, `updated_at`) VALUES
(17, 4, 'Lead Masuk', 1, 1, 'purple', '2026-08-20 01:07:09', '2026-08-23 22:26:14'),
(18, 4, 'Diskusi', 2, 0, 'purple', '2026-08-20 01:09:15', '2026-08-23 22:26:14'),
(26, 4, 'Tanya Spek Produk', 3, 0, 'purple', '2026-08-20 01:15:56', '2026-08-23 22:26:14'),
(27, 4, 'Tanya Harga', 4, 0, 'purple', '2026-08-20 01:16:02', '2026-08-23 22:26:14'),
(29, 4, 'Sudah dapat lokasi', 5, 0, 'purple', '2026-08-20 01:17:04', '2026-08-23 22:26:14'),
(30, 4, 'Meeting Call', 6, 0, 'purple', '2026-08-20 01:17:24', '2026-08-23 22:26:14'),
(31, 4, 'SPAM', 7, 0, 'purple', '2026-08-20 01:33:18', '2026-08-23 22:26:14'),
(40, 7, 'Lead Masuk', 1, 1, 'purple', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(41, 7, 'Meeting Call', 2, 0, 'blue', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(42, 7, 'Kirim Penawaran', 3, 0, 'yellow', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(43, 7, 'Deal', 4, 0, 'green', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(44, 8, 'Lead Masuk', 1, 1, 'purple', '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(45, 8, 'Meeting Call', 2, 0, 'blue', '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(46, 8, 'Kirim Penawaran', 3, 0, 'yellow', '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(47, 8, 'Deal', 4, 0, 'green', '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(48, 9, 'Lead Masuk', 1, 1, 'purple', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(49, 9, 'Meeting Call', 2, 0, 'blue', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(50, 9, 'Kirim Penawaran', 3, 0, 'yellow', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(51, 9, 'Deal', 4, 0, 'green', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(52, 10, 'Lead Masuk', 1, 1, 'purple', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(53, 10, 'Meeting Call', 2, 0, 'blue', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(54, 10, 'Kirim Penawaran', 3, 0, 'yellow', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(55, 10, 'Deal', 4, 0, 'green', '2026-08-31 00:51:31', '2026-08-31 00:51:31');

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

-- --------------------------------------------------------

--
-- Table structure for table `smtp_settings`
--

CREATE TABLE `smtp_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mail_mailer` varchar(255) NOT NULL DEFAULT 'smtp',
  `mail_host` varchar(255) DEFAULT NULL,
  `mail_port` int(11) NOT NULL DEFAULT 587,
  `mail_username` varchar(255) DEFAULT NULL,
  `mail_password` varchar(255) DEFAULT NULL,
  `mail_encryption` varchar(255) DEFAULT 'tls',
  `mail_from_address` varchar(255) NOT NULL DEFAULT 'no-reply@difitech.id',
  `mail_from_name` varchar(255) NOT NULL DEFAULT 'Difitech CRM Alert',
  `disconnect_alert_emails` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `smtp_settings`
--

INSERT INTO `smtp_settings` (`id`, `mail_mailer`, `mail_host`, `mail_port`, `mail_username`, `mail_password`, `mail_encryption`, `mail_from_address`, `mail_from_name`, `disconnect_alert_emails`, `created_at`, `updated_at`) VALUES
(1, 'smtp', 'smtp.gmail.com', 587, NULL, NULL, 'tls', 'no-reply@difitech.id', 'Difitech CRM Alert', 'wijaya@difitech.co.id, ashabil@difitech.co.id, siswandi@difitech.co.id, marketing2wkm@gmail.com,\nmarketingwkm@gmail.com', '2026-08-13 21:22:53', '2026-09-01 01:53:46');

-- --------------------------------------------------------

--
-- Table structure for table `stage_triggers`
--

CREATE TABLE `stage_triggers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wa_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pipeline_stage_id` bigint(20) UNSIGNED NOT NULL,
  `keyword` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stage_triggers`
--

INSERT INTO `stage_triggers` (`id`, `wa_account_id`, `pipeline_stage_id`, `keyword`, `created_at`, `updated_at`) VALUES
(25, 4, 18, 'dengan siapa dari mana ?', '2026-08-20 01:19:58', '2026-08-20 01:19:58'),
(27, 4, 18, 'boleh saya tahu kebutuhan', '2026-08-20 01:24:33', '2026-08-20 01:24:33'),
(28, 4, 18, 'boleh saya bantu konsultasikan?', '2026-08-20 01:25:14', '2026-08-20 01:25:14'),
(29, 4, 26, 'detail kebutuhan produknya?', '2026-08-20 01:26:03', '2026-08-20 01:26:03'),
(30, 4, 26, 'spesifikasi yang dicari?', '2026-08-20 01:26:46', '2026-08-20 01:26:46'),
(31, 4, 27, 'bantu cek estimasi harganya ya', '2026-08-20 01:27:32', '2026-08-20 01:27:32'),
(32, 4, 27, 'saya hitungkan estimasi harganya', '2026-08-20 01:27:52', '2026-08-20 01:27:52'),
(33, 4, 27, 'bantu cek penawaran terbaiknya', '2026-08-20 01:28:21', '2026-08-20 01:28:21'),
(34, 4, 29, 'alamat lokasinya sudah kami catat', '2026-08-20 01:29:57', '2026-08-20 01:29:57'),
(35, 4, 29, 'detail lokasinya sudah kami catat', '2026-08-20 01:30:21', '2026-08-20 01:30:21'),
(36, 4, 30, 'tim kami akan segera menghubungi', '2026-08-20 01:32:17', '2026-08-20 01:32:17'),
(37, 4, 31, 'mohon maaf, kami tidak dapat melanjutkan percakapan', '2026-08-20 01:34:59', '2026-08-20 01:34:59'),
(50, 7, 41, 'meeting', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(51, 7, 41, 'call', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(52, 7, 42, 'penawaran', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(53, 7, 42, 'proposal', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(54, 7, 43, 'deal', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(55, 7, 43, 'lunas', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(62, 9, 49, 'meeting', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(63, 9, 49, 'call', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(64, 9, 50, 'penawaran', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(65, 9, 50, 'proposal', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(66, 9, 51, 'deal', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(67, 9, 51, 'lunas', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(68, 10, 53, 'meeting', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(69, 10, 53, 'call', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(70, 10, 54, 'penawaran', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(71, 10, 54, 'proposal', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(72, 10, 55, 'deal', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(73, 10, 55, 'lunas', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(74, 8, 45, 'terima kasih sudah diijinkan telepon', '2026-09-01 00:46:47', '2026-09-01 00:46:47'),
(75, 8, 46, 'berikut penawaran terlampir', '2026-09-01 00:47:06', '2026-09-01 00:47:06'),
(76, 8, 47, 'terima kasih untuk pembayaran-nya', '2026-09-01 00:47:21', '2026-09-01 00:47:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'ADMIN',
  `status` varchar(255) NOT NULL DEFAULT 'PENDING',
  `wa_account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `wa_status` varchar(255) NOT NULL DEFAULT 'DISCONNECTED',
  `wa_phone` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `status`, `wa_account_id`, `session_id`, `wa_status`, `wa_phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'CEO / Owner (Wijaya)', 'wijaya@difitech.co.id', NULL, 'CEO', 'APPROVED', NULL, 'session_user_1', 'DISCONNECTED', NULL, NULL, '$2y$12$786dM02HEyUQet7/oiAjleJpaQxIMlyk4/EtEW6566iYAUQ8rQt9e', 'AJIyYxXJEL1pIXS0ta7KX8vxca7bCAtJGkW1FTJiRiXBDa5MCztJcNKUaGUn', '2026-08-13 23:58:53', '2026-08-26 00:37:35'),
(3, 'Siswandi', 'siswandi@difitech.co.id', '087733516564', 'SUPERVISOR', 'APPROVED', 4, 'session_user_3', 'DISCONNECTED', NULL, NULL, '$2y$12$dIxrwpWJXWmmjpsEmnL2Au2KsUJzcTTsOK5X9oAkilVyHejgsRRo6', '4eeBXAoz9LPvojyqX8OD6ABy2ksP5ohHm9oe2sJ7AgcOjsuGkK2DXp9dLtey', '2026-08-19 00:32:27', '2026-08-26 22:52:13'),
(4, 'Amanda', 'amandadifitech@gmail.com', NULL, 'SALES_ADMIN', 'APPROVED', 4, 'session_user_4', 'CONNECTED', '6285772053530', NULL, '$2y$12$7ZcNPuSKIW981oUNd82FJeK5tObOB6WnmDNuUa7V69TDe2pEx2r.i', 'pQYbtPt5tjO7aylsPn6d9NAuxrFy20mMowSFsHCUr81tzYGd9FNqcLq9kmvK', '2026-08-19 00:52:42', '2026-08-28 02:33:44'),
(5, 'Bu Amel', 'marketingwkm@gmail.com', '6285746338899', 'SALES_ADMIN', 'APPROVED', 8, 'session_user_5', 'CONNECTED', '6285746338899', NULL, '$2y$12$AsdegtKNRvydWYl55zwTmecOstuK.kkXMs2vh5Ps1Lcc9VHK7yigq', 'QDzsEMXKNmCCxUPT90VyeGU81pUznYBscTRczdQrFyHpa7E6TEgiPUpGcvj2', '2026-09-01 00:05:52', '2026-09-01 01:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `wa_accounts`
--

CREATE TABLE `wa_accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'DISCONNECTED',
  `approval_status` varchar(255) NOT NULL DEFAULT 'APPROVED',
  `supervisor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `disconnect_email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `disconnect_email_interval` int(11) NOT NULL DEFAULT 10,
  `disconnect_alert_emails` text DEFAULT NULL,
  `last_disconnect_email_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wa_accounts`
--

INSERT INTO `wa_accounts` (`id`, `name`, `category`, `phone`, `session_id`, `status`, `approval_status`, `supervisor_id`, `disconnect_email_enabled`, `disconnect_email_interval`, `disconnect_alert_emails`, `last_disconnect_email_sent_at`, `created_at`, `updated_at`) VALUES
(4, 'Nabata Carpet', 'General Business', NULL, 'session_brand_1787124747', 'DISCONNECTED', 'APPROVED', 3, 1, 1800, NULL, NULL, '2026-08-19 00:32:27', '2026-08-26 21:04:57'),
(7, 'testing brand', 'General Business', NULL, 'session_1788160765', 'DISCONNECTED', 'APPROVED', NULL, 1, 10, NULL, NULL, '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(8, 'WKM', 'General Business', '62895638871717', 'session_1788161621', 'DISCONNECTED', 'APPROVED', 3, 1, 10, NULL, NULL, '2026-08-31 00:33:41', '2026-09-01 00:05:52'),
(9, 'Calmara', 'General Business', NULL, 'session_1788162680', 'DISCONNECTED', 'APPROVED', NULL, 1, 10, NULL, NULL, '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(10, 'PPF dirumah', 'General Business', NULL, 'session_1788162691', 'DISCONNECTED', 'APPROVED', NULL, 1, 10, NULL, NULL, '2026-08-31 00:51:31', '2026-08-31 00:51:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_lead_comparisons`
--
ALTER TABLE `ai_lead_comparisons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brand_supervisors`
--
ALTER TABLE `brand_supervisors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `brand_supervisors_user_id_wa_account_id_unique` (`user_id`,`wa_account_id`),
  ADD KEY `brand_supervisors_wa_account_id_foreign` (`wa_account_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

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
  ADD UNIQUE KEY `leads_phone_unique` (`phone`),
  ADD KEY `leads_wa_account_id_foreign` (`wa_account_id`),
  ADD KEY `leads_assigned_user_id_foreign` (`assigned_user_id`);

--
-- Indexes for table `lead_messages`
--
ALTER TABLE `lead_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_messages_lead_id_foreign` (`lead_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pipeline_stages_wa_account_id_foreign` (`wa_account_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `smtp_settings`
--
ALTER TABLE `smtp_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stage_triggers`
--
ALTER TABLE `stage_triggers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stage_triggers_wa_account_id_foreign` (`wa_account_id`),
  ADD KEY `stage_triggers_pipeline_stage_id_foreign` (`pipeline_stage_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_session_id_unique` (`session_id`),
  ADD KEY `users_wa_account_id_foreign` (`wa_account_id`);

--
-- Indexes for table `wa_accounts`
--
ALTER TABLE `wa_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wa_accounts_session_id_unique` (`session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_lead_comparisons`
--
ALTER TABLE `ai_lead_comparisons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `brand_supervisors`
--
ALTER TABLE `brand_supervisors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `lead_messages`
--
ALTER TABLE `lead_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `smtp_settings`
--
ALTER TABLE `smtp_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stage_triggers`
--
ALTER TABLE `stage_triggers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `wa_accounts`
--
ALTER TABLE `wa_accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `brand_supervisors`
--
ALTER TABLE `brand_supervisors`
  ADD CONSTRAINT `brand_supervisors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `brand_supervisors_wa_account_id_foreign` FOREIGN KEY (`wa_account_id`) REFERENCES `wa_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_assigned_user_id_foreign` FOREIGN KEY (`assigned_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `leads_wa_account_id_foreign` FOREIGN KEY (`wa_account_id`) REFERENCES `wa_accounts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lead_messages`
--
ALTER TABLE `lead_messages`
  ADD CONSTRAINT `lead_messages_lead_id_foreign` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pipeline_stages`
--
ALTER TABLE `pipeline_stages`
  ADD CONSTRAINT `pipeline_stages_wa_account_id_foreign` FOREIGN KEY (`wa_account_id`) REFERENCES `wa_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stage_triggers`
--
ALTER TABLE `stage_triggers`
  ADD CONSTRAINT `stage_triggers_pipeline_stage_id_foreign` FOREIGN KEY (`pipeline_stage_id`) REFERENCES `pipeline_stages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stage_triggers_wa_account_id_foreign` FOREIGN KEY (`wa_account_id`) REFERENCES `wa_accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_wa_account_id_foreign` FOREIGN KEY (`wa_account_id`) REFERENCES `wa_accounts` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
