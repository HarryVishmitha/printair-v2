-- Additive SQL only (no ALTER/DROP).
-- Intended for MySQL/InnoDB with utf8mb4_unicode_ci, consistent with existing dump style.
--
-- Notes / future-proofing tweaks vs original proposal:
-- - `estimate_no`, `order_no`, `invoice_no` are unique per `working_group_id` (not globally).
-- - Added `payments.created_by` + `payments.updated_by` to support auditability (FK to users).
-- - Added a few practical composite indexes for common list/sort patterns.

-- =========================
-- ESTIMATES
-- =========================
CREATE TABLE IF NOT EXISTS `estimates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimate_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_group_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,

  `customer_snapshot` json DEFAULT NULL,

  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',
  `price_tier_id` bigint unsigned DEFAULT NULL,

  `subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0,
  `other_fee` decimal(12,2) NOT NULL DEFAULT 0,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0,

  `tax_mode` enum('none','inclusive','exclusive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `discount_mode` enum('none','percent','amount') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0,

  `status` enum('draft','sent','viewed','accepted','rejected','expired','cancelled','converted') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `valid_until` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `accepted_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `converted_at` datetime DEFAULT NULL,

  `locked_at` datetime DEFAULT NULL,
  `locked_by` bigint unsigned DEFAULT NULL,

  `revision` int unsigned NOT NULL DEFAULT 1,
  `parent_estimate_id` bigint unsigned DEFAULT NULL,

  `notes_internal` text COLLATE utf8mb4_unicode_ci,
  `notes_customer` text COLLATE utf8mb4_unicode_ci,
  `terms` longtext COLLATE utf8mb4_unicode_ci,
  `meta` json DEFAULT NULL,

  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `estimates_uuid_unique` (`uuid`),
  UNIQUE KEY `estimates_wg_estimate_no_unique` (`working_group_id`,`estimate_no`),
  UNIQUE KEY `estimates_parent_revision_unique` (`parent_estimate_id`,`revision`),
  KEY `estimates_wg_status_index` (`working_group_id`,`status`),
  KEY `estimates_customer_id_index` (`customer_id`),
  KEY `estimates_created_by_index` (`created_by`),
  KEY `estimates_locked_at_index` (`locked_at`),

  CONSTRAINT `estimates_working_group_id_foreign` FOREIGN KEY (`working_group_id`) REFERENCES `working_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `estimates_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `estimates_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `estimates_parent_estimate_id_foreign` FOREIGN KEY (`parent_estimate_id`) REFERENCES `estimates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `estimates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `estimates_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `estimate_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `estimate_id` bigint unsigned NOT NULL,
  `working_group_id` bigint unsigned NOT NULL,

  `product_id` bigint unsigned NOT NULL,
  `variant_set_item_id` bigint unsigned DEFAULT NULL,
  `roll_id` bigint unsigned DEFAULT NULL,

  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,

  `qty` int unsigned NOT NULL DEFAULT 1,

  `width` decimal(10,3) DEFAULT NULL,
  `height` decimal(10,3) DEFAULT NULL,
  `unit` enum('mm','cm','in','ft','m') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_sqft` decimal(12,4) DEFAULT NULL,
  `offcut_sqft` decimal(12,4) NOT NULL DEFAULT 0,

  `unit_price` decimal(12,2) NOT NULL DEFAULT 0,
  `line_subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0,

  `pricing_snapshot` json NOT NULL,

  `sort_order` int NOT NULL DEFAULT 0,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `estimate_items_estimate_id_index` (`estimate_id`),
  KEY `estimate_items_estimate_sort_index` (`estimate_id`,`sort_order`),
  KEY `estimate_items_product_id_index` (`product_id`),
  KEY `estimate_items_wg_index` (`working_group_id`),

  CONSTRAINT `estimate_items_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estimate_items_working_group_id_foreign` FOREIGN KEY (`working_group_id`) REFERENCES `working_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `estimate_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `estimate_items_variant_set_item_id_foreign` FOREIGN KEY (`variant_set_item_id`) REFERENCES `product_variant_set_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `estimate_items_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `estimate_item_finishings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `estimate_item_id` bigint unsigned NOT NULL,
  `finishing_product_id` bigint unsigned NOT NULL,
  `option_id` bigint unsigned DEFAULT NULL,

  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int unsigned NOT NULL DEFAULT 1,

  `unit_price` decimal(12,2) NOT NULL DEFAULT 0,
  `total` decimal(12,2) NOT NULL DEFAULT 0,

  `pricing_snapshot` json DEFAULT NULL,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `est_item_finishings_item_id_index` (`estimate_item_id`),

  CONSTRAINT `est_item_finishings_item_id_foreign` FOREIGN KEY (`estimate_item_id`) REFERENCES `estimate_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `est_item_finishings_finishing_product_id_foreign` FOREIGN KEY (`finishing_product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `est_item_finishings_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `estimate_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `estimate_id` bigint unsigned NOT NULL,
  `from_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `est_status_hist_estimate_id_index` (`estimate_id`),
  KEY `est_status_hist_estimate_created_index` (`estimate_id`,`created_at`),

  CONSTRAINT `est_status_hist_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `est_status_hist_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `estimate_shares` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `estimate_id` bigint unsigned NOT NULL,
  `token_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `revoked_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `last_accessed_at` datetime DEFAULT NULL,
  `access_count` int unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `estimate_shares_token_hash_unique` (`token_hash`),
  KEY `estimate_shares_estimate_id_index` (`estimate_id`),
  KEY `estimate_shares_estimate_expires_index` (`estimate_id`,`expires_at`),

  CONSTRAINT `estimate_shares_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `estimate_shares_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- ORDERS
-- =========================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_group_id` bigint unsigned NOT NULL,

  `customer_id` bigint unsigned DEFAULT NULL,
  `estimate_id` bigint unsigned DEFAULT NULL,
  `customer_snapshot` json DEFAULT NULL,

  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',

  `subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0,
  `other_fee` decimal(12,2) NOT NULL DEFAULT 0,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0,

  `status` enum('draft','confirmed','in_production','ready','out_for_delivery','completed','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `payment_status` enum('unpaid','partial','paid','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',

  `ordered_at` datetime DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,

  `locked_at` datetime DEFAULT NULL,
  `locked_by` bigint unsigned DEFAULT NULL,

  `meta` json DEFAULT NULL,

  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_uuid_unique` (`uuid`),
  UNIQUE KEY `orders_wg_order_no_unique` (`working_group_id`,`order_no`),
  KEY `orders_wg_status_index` (`working_group_id`,`status`),
  KEY `orders_customer_id_index` (`customer_id`),
  KEY `orders_estimate_id_index` (`estimate_id`),

  CONSTRAINT `orders_working_group_id_foreign` FOREIGN KEY (`working_group_id`) REFERENCES `working_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_estimate_id_foreign` FOREIGN KEY (`estimate_id`) REFERENCES `estimates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `orders_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `working_group_id` bigint unsigned NOT NULL,

  `product_id` bigint unsigned NOT NULL,
  `variant_set_item_id` bigint unsigned DEFAULT NULL,
  `roll_id` bigint unsigned DEFAULT NULL,

  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,

  `qty` int unsigned NOT NULL DEFAULT 1,

  `width` decimal(10,3) DEFAULT NULL,
  `height` decimal(10,3) DEFAULT NULL,
  `unit` enum('mm','cm','in','ft','m') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_sqft` decimal(12,4) DEFAULT NULL,
  `offcut_sqft` decimal(12,4) NOT NULL DEFAULT 0,

  `unit_price` decimal(12,2) NOT NULL DEFAULT 0,
  `line_subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0,

  `pricing_snapshot` json NOT NULL,
  `sort_order` int NOT NULL DEFAULT 0,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_order_sort_index` (`order_id`,`sort_order`),
  KEY `order_items_product_id_index` (`product_id`),
  KEY `order_items_wg_index` (`working_group_id`),

  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_working_group_id_foreign` FOREIGN KEY (`working_group_id`) REFERENCES `working_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `order_items_variant_set_item_id_foreign` FOREIGN KEY (`variant_set_item_id`) REFERENCES `product_variant_set_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `order_item_finishings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_item_id` bigint unsigned NOT NULL,
  `finishing_product_id` bigint unsigned NOT NULL,
  `option_id` bigint unsigned DEFAULT NULL,

  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int unsigned NOT NULL DEFAULT 1,

  `unit_price` decimal(12,2) NOT NULL DEFAULT 0,
  `total` decimal(12,2) NOT NULL DEFAULT 0,

  `pricing_snapshot` json DEFAULT NULL,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `order_item_finishings_order_item_id_index` (`order_item_id`),

  CONSTRAINT `order_item_finishings_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_item_finishings_finishing_product_id_foreign` FOREIGN KEY (`finishing_product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `order_item_finishings_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `options` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `order_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `from_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `order_status_histories_order_id_index` (`order_id`),
  KEY `order_status_histories_order_created_index` (`order_id`,`created_at`),

  CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- INVOICES
-- =========================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,

  `working_group_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned NOT NULL,

  `type` enum('final','partial','credit_note') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'final',
  `status` enum('draft','issued','void','paid','partial','overdue','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',

  `issued_at` datetime DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `voided_at` datetime DEFAULT NULL,

  `locked_at` datetime DEFAULT NULL,
  `locked_by` bigint unsigned DEFAULT NULL,

  `customer_snapshot` json DEFAULT NULL,

  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount_total` decimal(12,2) NOT NULL DEFAULT 0,
  `tax_total` decimal(12,2) NOT NULL DEFAULT 0,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0,
  `other_fee` decimal(12,2) NOT NULL DEFAULT 0,
  `grand_total` decimal(12,2) NOT NULL DEFAULT 0,

  `amount_paid` decimal(12,2) NOT NULL DEFAULT 0,
  `amount_due` decimal(12,2) NOT NULL DEFAULT 0,

  `meta` json DEFAULT NULL,

  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `invoices_uuid_unique` (`uuid`),
  UNIQUE KEY `invoices_wg_invoice_no_unique` (`working_group_id`,`invoice_no`),
  KEY `invoices_wg_status_index` (`working_group_id`,`status`),
  KEY `invoices_locked_at_index` (`locked_at`),
  KEY `invoices_order_id_index` (`order_id`),

  CONSTRAINT `invoices_working_group_id_foreign` FOREIGN KEY (`working_group_id`) REFERENCES `working_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_locked_by_foreign` FOREIGN KEY (`locked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoices_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoices_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `order_item_id` bigint unsigned DEFAULT NULL,

  `working_group_id` bigint unsigned NOT NULL,

  `product_id` bigint unsigned NOT NULL,
  `variant_set_item_id` bigint unsigned DEFAULT NULL,
  `roll_id` bigint unsigned DEFAULT NULL,

  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,

  `qty` int unsigned NOT NULL DEFAULT 1,

  `width` decimal(10,3) DEFAULT NULL,
  `height` decimal(10,3) DEFAULT NULL,
  `unit` enum('mm','cm','in','ft','m') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `area_sqft` decimal(12,4) DEFAULT NULL,
  `offcut_sqft` decimal(12,4) NOT NULL DEFAULT 0,

  `unit_price` decimal(12,2) NOT NULL DEFAULT 0,
  `line_subtotal` decimal(12,2) NOT NULL DEFAULT 0,
  `discount_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `tax_amount` decimal(12,2) NOT NULL DEFAULT 0,
  `line_total` decimal(12,2) NOT NULL DEFAULT 0,

  `pricing_snapshot` json NOT NULL,

  `sort_order` int NOT NULL DEFAULT 0,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `invoice_items_invoice_id_index` (`invoice_id`),
  KEY `invoice_items_working_group_id_index` (`working_group_id`),
  KEY `invoice_items_wg_product_id_index` (`working_group_id`,`product_id`),
  KEY `invoice_items_wg_created_at_index` (`working_group_id`,`created_at`),
  KEY `invoice_items_order_item_id_index` (`order_item_id`),
  KEY `invoice_items_invoice_sort_index` (`invoice_id`,`sort_order`),

  CONSTRAINT `invoice_items_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_items_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_items_working_group_id_foreign` FOREIGN KEY (`working_group_id`) REFERENCES `working_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoice_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `invoice_items_variant_set_item_id_foreign` FOREIGN KEY (`variant_set_item_id`) REFERENCES `product_variant_set_items` (`id`) ON DELETE SET NULL,
  CONSTRAINT `invoice_items_roll_id_foreign` FOREIGN KEY (`roll_id`) REFERENCES `rolls` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `invoice_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` bigint unsigned NOT NULL,
  `from_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `reason` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  KEY `invoice_status_histories_invoice_id_index` (`invoice_id`),
  KEY `invoice_status_histories_invoice_created_index` (`invoice_id`,`created_at`),

  CONSTRAINT `invoice_status_histories_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `invoice_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- PAYMENTS + ALLOCATION
-- =========================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,

  `working_group_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,

  `method` enum('cash','card','bank_transfer','online_gateway') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','confirmed','failed','void','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',

  `amount` decimal(12,2) NOT NULL DEFAULT 0,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'LKR',

  `reference_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `received_by` bigint unsigned DEFAULT NULL,

  `meta` json DEFAULT NULL,

  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,

  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_uuid_unique` (`uuid`),
  KEY `payments_wg_index` (`working_group_id`),
  KEY `payments_wg_status_index` (`working_group_id`,`status`),
  KEY `payments_customer_id_index` (`customer_id`),
  KEY `payments_reference_no_index` (`reference_no`),
  KEY `payments_received_at_index` (`received_at`),

  CONSTRAINT `payments_working_group_id_foreign` FOREIGN KEY (`working_group_id`) REFERENCES `working_groups` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `payment_allocations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint unsigned NOT NULL,
  `invoice_id` bigint unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,

  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_allocations_payment_invoice_unique` (`payment_id`,`invoice_id`),
  KEY `payment_allocations_payment_id_index` (`payment_id`),
  KEY `payment_allocations_invoice_id_index` (`invoice_id`),

  CONSTRAINT `payment_allocations_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_allocations_invoice_id_foreign` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_allocations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
