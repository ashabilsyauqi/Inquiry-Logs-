SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users
INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `status`, `wa_account_id`, `session_id`, `wa_status`, `wa_phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'CEO / Owner (Wijaya)', 'wijaya@difitech.co.id', NULL, 'CEO', 'APPROVED', NULL, 'session_user_1', 'DISCONNECTED', NULL, NULL, '$2y$12$786dM02HEyUQet7/oiAjleJpaQxIMlyk4/EtEW6566iYAUQ8rQt9e', 'AJIyYxXJEL1pIXS0ta7KX8vxca7bCAtJGkW1FTJiRiXBDa5MCztJcNKUaGUn', '2026-08-13 23:58:53', '2026-08-26 00:37:35'),
(3, 'Siswandi', 'siswandi@difitech.co.id', '087733516564', 'SUPERVISOR', 'APPROVED', 4, 'session_user_3', 'DISCONNECTED', NULL, NULL, '$2y$12$dIxrwpWJXWmmjpsEmnL2Au2KsUJzcTTsOK5X9oAkilVyHejgsRRo6', '4eeBXAoz9LPvojyqX8OD6ABy2ksP5ohHm9oe2sJ7AgcOjsuGkK2DXp9dLtey', '2026-08-19 00:32:27', '2026-08-26 22:52:13'),
(4, 'Amanda', 'amandadifitech@gmail.com', NULL, 'SALES_ADMIN', 'APPROVED', 4, 'session_user_4', 'CONNECTED', '6285772053530', NULL, '$2y$12$7ZcNPuSKIW981oUNd82FJeK5tObOB6WnmDNuUa7V69TDe2pEx2r.i', 'pQYbtPt5tjO7aylsPn6d9NAuxrFy20mMowSFsHCUr81tzYGd9FNqcLq9kmvK', '2026-08-19 00:52:42', '2026-08-28 02:33:44'),
(5, 'Bu Amel', 'marketingwkm@gmail.com', '6285746338899', 'SALES_ADMIN', 'APPROVED', 8, 'session_user_5', 'CONNECTED', '6285746338899', NULL, '$2y$12$AsdegtKNRvydWYl55zwTmecOstuK.kkXMs2vh5Ps1Lcc9VHK7yigq', 'QDzsEMXKNmCCxUPT90VyeGU81pUznYBscTRczdQrFyHpa7E6TEgiPUpGcvj2', '2026-09-01 00:05:52', '2026-09-01 01:51:09');

-- 2. WA Accounts (Brands)
INSERT INTO `wa_accounts` (`id`, `name`, `category`, `phone`, `session_id`, `status`, `approval_status`, `supervisor_id`, `disconnect_email_enabled`, `disconnect_email_interval`, `disconnect_alert_emails`, `last_disconnect_email_sent_at`, `created_at`, `updated_at`) VALUES
(4, 'Nabata Carpet', 'General Business', NULL, 'session_brand_1787124747', 'DISCONNECTED', 'APPROVED', 3, 1, 1800, NULL, NULL, '2026-08-19 00:32:27', '2026-08-26 21:04:57'),
(7, 'testing brand', 'General Business', NULL, 'session_1788160765', 'DISCONNECTED', 'APPROVED', NULL, 1, 10, NULL, NULL, '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(8, 'WKM', 'General Business', '62895638871717', 'session_1788161621', 'DISCONNECTED', 'APPROVED', 3, 1, 10, NULL, NULL, '2026-08-31 00:33:41', '2026-09-01 00:05:52'),
(9, 'Calmara', 'General Business', NULL, 'session_1788162680', 'DISCONNECTED', 'APPROVED', NULL, 1, 10, NULL, NULL, '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(10, 'PPF dirumah', 'General Business', NULL, 'session_1788162691', 'DISCONNECTED', 'APPROVED', NULL, 1, 10, NULL, NULL, '2026-08-31 00:51:31', '2026-08-31 00:51:31');

-- 3. Brand Supervisors (Multi-Brand Mapping)
INSERT INTO `brand_supervisors` (`id`, `user_id`, `wa_account_id`, `created_at`, `updated_at`) VALUES
(1, 3, 4, '2026-08-30 23:45:46', '2026-08-30 23:45:46'),
(3, 3, 7, '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(4, 3, 8, '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(5, 3, 9, '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(6, 3, 10, '2026-08-31 00:51:31', '2026-08-31 00:51:31');

-- 4. Pipeline Stages
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

-- 5. Stage Triggers
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
(51, 7, 42, 'penawaran', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(52, 7, 43, 'deal', '2026-08-31 00:19:25', '2026-08-31 00:19:25'),
(53, 8, 45, 'meeting', '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(54, 8, 46, 'penawaran', '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(55, 8, 47, 'deal', '2026-08-31 00:33:41', '2026-08-31 00:33:41'),
(56, 9, 49, 'meeting', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(57, 9, 50, 'penawaran', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(58, 9, 51, 'deal', '2026-08-31 00:51:20', '2026-08-31 00:51:20'),
(59, 10, 53, 'meeting', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(60, 10, 54, 'penawaran', '2026-08-31 00:51:31', '2026-08-31 00:51:31'),
(61, 10, 55, 'deal', '2026-08-31 00:51:31', '2026-08-31 00:51:31');

-- 6. Leads
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

-- 7. Lead Messages
INSERT INTO `lead_messages` (`id`, `lead_id`, `sender`, `message`, `is_from_me`, `created_at`, `updated_at`) VALUES
(1, 1, '255580937679063', 'LIST AREA SAMEDAY JKT-BEKASI & LUAR KOTA (PAXEL) DIKIRIM DARI GADING:\n1. ⁠Lukman Kharish - Mounjaro 5mg - Payment 7 Agustus - PU Klinik tanggal 18 Agustus - Aizza\n2. Angie Witara - Mounjaro 5mg - Jakarta - pay 10 ag - O\n3. Tiffany - Mounjaro 2.5mg - jkt - pay 11 agust - Cintia\n4. ⁠⁠Rendi - Mounjaro 2.5mg - jkt - pay 11 ags - liana\n5. Vivi Irawaty - Wegovy 0,25 - PU Klinik - pay 11 Agustus - Poppy\n6. Edwin H - mounjaro 2.5mg - sameday - pay 11 agus - lia\n7. Ifada Zain - Mounjaro 2,5mg - Paxel - pay 11 agust - nandita\n8. vonny - mounjaro 2,5mg - pick up klinik - pay 11 agust - nandita\n9. Felicia - ACIP 2,5 mg - pay 11 ags - JKT - Tiara\n10. Dessy Jusuf - Mounjaro 5mg - pay 11 Agust - PAXEL JGJ - S\n11. ⁠Yurike - Mounjaro 2.5mg - pay 12 agust - Paxel Semarang - liana\n12. ⁠Aishah - Mounjaro 5mg - payment 12 Agustus - JKT - Sameday - Siska\n13. ⁠Maya Wardhana - Wegovy 1mg + Wegovyy 1mg - Pay 12 ag - Jakarta - O\n14. ⁠Nachi  - Mounjaro 5mg - pay 12 Agust - JKT - S\n15. ⁠Drg Farah Swastika - Mounjaro 5mg- Paxel Aceh Barat - pay 12 Agustus - Amanda \n16. ⁠Sitti Fatrah - Mounjaro 5mg - Paxel Counter Gorontalo - pay 13 agt - Liana\n17. ⁠Andita Destiarini - Mounjaro 2.5mg - PU Klinik - Pay 13 Aug - Hani\n18. ⁠Teng Vincent Jonathan - Wegovy 0,25 - PU Klinik - pay 13 agt - Poppy\n19. ⁠Rizky Pravianti - Mounjaro 2,5mg - sidoarjo paxel 13 agustus - lia\n20. ⁠Esmaralda Nurul Amany - Mounjaro 5mg - Pay 13 Agustus - Kota Baru, Jambi (Paxel) - Siska\n21. ⁠Putu gede Narendra SD - Mounjaro 2,5mg - Paxel bali - pay 13 agt - karin \n22. ⁠Nurul Fachirah - Wegovy 0.5mg - pay 13 Agust\n59. Herlina - Wegovy 0.5mg - pay 16 Agust - JKT - S\n60. Nadia Jasmine -  Mounjaro 5mg - pay 18 Agust - PAXEL BDG - S\n125. Kurniata Sekararum - Mounjaro 5mg - pay 18 Agust - PAXEL BDG - S\n126. Sandra - Mounjaro 2,5mg - Pay 18 Agust - JAKBAR - AMELIA\n127. Ari Delima - Wegovy 0,25 mg - pay 19 Agustus - paxel counter pekanbaru - Hani\n128. Rima M - Wegovy 0,25 mg - pay 19 Agustus - paxel medan - Hani\n129. Roy Stagg - Wegovy 2.4mg - Jakarta - pay 19 130. Lutfi Aziz - Wegovy 0.5mg - Paxel lampung - pay 19 ag - O\n131. Chairunisa Oktaviani - Wegovy 1mg - PU klinik - pay 19 ag - O (istri)\n\n9. Agreini Dwi Erza - Mounjaro 5mg - pay 16 Agust - TGR - S\n10. ⁠Sumiati - ACIP 2.5 mg - TGR - Tiara\n11. Nita - Mounjaro 2.5mg. -injek di bsd - pay 17 agust - cintia\n12. Maria Dewi - ACI 0,5 mg - pay 17 Agustus - Tangerang - Amanda \n13. Yasinda Arga - Wegovy 1.7mg - pay 17 Agust - DPK - S\n14. Gita Dwinta Sari - Wegovy 0.5mg - pay 17 Agust - DPK - S\n15. Dini Suryabrata - Wegovy 1.7mg - pay 17 Agust - TGR - S\n16. Eva Tanura - Wegovy 1mg - pay 17 Agust - TGR - S\n17. Liya - Wegovy 0.5mg - pay 17 Agust - TGR - S\n18. Irsyad Sahroni - Mounjaro 5mg - pay 17 Agust - TGR - S\n19. Tri Sutrisno - ACIP 2,5MG - PAY 18 AGUS - PU BSD 22 AGUS 2026 - ALMA\n20. RIZKA FAUZIAH - ACIP 2,5MG - PAY 18 AGUS - PU BSD 22 AGUS 2026 - ALMA\n21. Novrida - ACI 0.25mg - Pay 18 Agustus - *Apotek Panel Sumatera* - Liana\n22. Markus Panjaitan - ACI 0.5mg - Pay 18 Agustus - TGR - Siska\n23. Arvina Noviawati - Wegovy 1mg - pay 18 Agust - *PANEL BINJAI* - S\n24. Neny Triana - Wegovy 0.5mg - pay 18 Agust - DPK - S\n25. Wilson Pascall - Mounjaro 2.5mg - pay 18 Agust - TGR - S\n26. Fatimah Alhabsyi - Wegovy 1.7mg - *panel bali ongkir 100k* - pay 19 ag - O\n27. MARIA ULFA - ACIP 2,5MG - PAY 19 AGUS - PU BSD - ALMA\n28. FERDINAND WIJAYA - ACIP 2,5MG - PAY 19 AGUS - PU BSD - ALMA\n29. Siham marwan - wego 0.25mg - bogor tengah - pay 19 agus - U\n30. Kodir - wego 0.25mg - tangsel - pay 19 agus - U\n\nPIC Nana', 0, '2026-08-19 03:56:36', '2026-08-19 03:56:36'),
(2, 1, '255580937679063', 'Assalamualaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai karpet masjid', 0, '2026-08-19 03:57:19', '2026-08-19 03:57:19'),
(3, 2, '274577343160505', 'Assalamualaikum wr wb, Saya mendapatkan informasi dari website nabata karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-20 03:40:38', '2026-08-20 03:40:38'),
(4, 3, '213215178940557', 'mba', 0, '2026-08-20 03:52:23', '2026-08-20 03:52:23'),
(5, 6, '78971597271125', 'Halo', 0, '2026-08-21 09:43:35', '2026-08-21 09:43:35'),
(6, 7, '30756277633265', 'Assalamualaiku', 0, '2026-08-22 02:12:10', '2026-08-22 02:12:10'),
(7, 8, '199200650342433', 'Assalamualaiakum', 0, '2026-08-22 05:02:34', '2026-08-22 05:02:34'),
(8, 9, '57076239847635', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-22 17:57:14', '2026-08-22 17:57:14'),
(9, 10, '232843682812131', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-22 22:02:49', '2026-08-22 22:02:49'),
(10, 11, '206149940969544', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-22 23:49:02', '2026-08-22 23:49:02'),
(11, 12, '40952915861625', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-23 05:37:59', '2026-08-23 05:37:59'),
(12, 13, '119305668681943', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-23 05:41:45', '2026-08-23 05:41:45'),
(13, 14, '245273284575451', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-23 23:47:05', '2026-08-23 23:47:05'),
(14, 15, '201658009141455', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-24 20:07:41', '2026-08-24 20:07:41'),
(15, 16, '144564505579721', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 02:34:34', '2026-08-28 02:34:34'),
(16, 17, '14285631701215', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 02:35:58', '2026-08-28 02:35:58'),
(17, 18, '18593500643509', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 02:36:10', '2026-08-28 02:36:10'),
(18, 19, '94966793658385', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 02:41:17', '2026-08-28 02:41:17'),
(19, 20, '267882076356659', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 02:45:00', '2026-08-28 02:45:00'),
(20, 21, '203036106457112', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 03:51:35', '2026-08-28 03:51:35'),
(21, 22, '48438674739434', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 04:00:06', '2026-08-28 04:00:06'),
(22, 23, '253566329585826', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 04:52:05', '2026-08-28 04:52:05'),
(23, 24, '97788385845368', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-28 05:20:43', '2026-08-28 05:20:43'),
(24, 25, '66194220572703', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-29 08:25:53', '2026-08-29 08:25:53'),
(25, 26, '12416800800896', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-29 16:33:52', '2026-08-29 16:33:52'),
(26, 27, '17627284004962', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan informasi dari website Nabata Karpet. Saya ingin konsultasi mengenai Karpet Masjid', 0, '2026-08-30 23:07:41', '2026-08-30 23:07:41'),
(27, 28, '16479923740792', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan info dari website WKM. Saya ingin konsultasi mengenai produk WKM', 0, '2026-09-01 02:28:41', '2026-09-01 02:28:41'),
(28, 29, '90298164224038', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan info dari website WKM. Saya ingin konsultasi mengenai produk WKM', 0, '2026-09-01 04:19:23', '2026-09-01 04:19:23'),
(29, 30, '34789386113092', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan info dari website WKM. Saya ingin konsultasi mengenai produk WKM', 0, '2026-09-01 05:22:50', '2026-09-01 05:22:50'),
(30, 31, '38594827821120', 'Assalamu\'alaikum Wr. Wb., Saya mendapatkan info dari website WKM. Saya ingin konsultasi mengenai produk WKM', 0, '2026-09-01 09:24:40', '2026-09-01 09:24:40');

-- 8. SMTP Settings
REPLACE INTO `smtp_settings` (`id`, `mail_mailer`, `mail_host`, `mail_port`, `mail_username`, `mail_password`, `mail_encryption`, `mail_from_address`, `mail_from_name`, `disconnect_alert_emails`, `created_at`, `updated_at`) VALUES
(1, 'smtp', 'smtp.gmail.com', 587, NULL, NULL, 'tls', 'no-reply@difitech.id', 'Difitech CRM Alert', 'wijaya@difitech.co.id, ashabil@difitech.co.id, siswandi@difitech.co.id, marketing2wkm@gmail.com,\nmarketingwkm@gmail.com', '2026-08-13 21:22:53', '2026-09-01 01:53:46');

SET FOREIGN_KEY_CHECKS = 1;
