-- BetaKos Phase 5: Midtrans Dynamic QRIS.
-- Non-destructive. Run after phase3_subscription_payment.sql and phase5_payment_method_config.sql.

ALTER TABLE pembayaran_langganan
  MODIFY COLUMN metode_pembayaran ENUM('transfer_bank','e_wallet','qris') NOT NULL;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND column_name = 'provider_pembayaran');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE pembayaran_langganan ADD COLUMN provider_pembayaran VARCHAR(30) NULL AFTER metode_pembayaran', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND column_name = 'provider_order_id');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE pembayaran_langganan ADD COLUMN provider_order_id VARCHAR(100) NULL AFTER provider_pembayaran', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND column_name = 'provider_transaction_id');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE pembayaran_langganan ADD COLUMN provider_transaction_id VARCHAR(100) NULL AFTER provider_order_id', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND column_name = 'provider_status');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE pembayaran_langganan ADD COLUMN provider_status VARCHAR(30) NULL AFTER provider_transaction_id', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND column_name = 'qr_string');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE pembayaran_langganan ADD COLUMN qr_string TEXT NULL AFTER provider_status', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND column_name = 'qr_code_url');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE pembayaran_langganan ADD COLUMN qr_code_url VARCHAR(500) NULL AFTER qr_string', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND column_name = 'paid_at');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE pembayaran_langganan ADD COLUMN paid_at DATETIME NULL AFTER qr_code_url', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND index_name = 'ux_pl_provider_order_id');
SET @sql := IF(@idx_exists = 0, 'ALTER TABLE pembayaran_langganan ADD UNIQUE INDEX ux_pl_provider_order_id (provider_order_id)', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'pembayaran_langganan' AND index_name = 'idx_pl_provider_status');
SET @sql := IF(@idx_exists = 0, 'ALTER TABLE pembayaran_langganan ADD INDEX idx_pl_provider_status (provider_status)', 'SELECT 1'); PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
