-- Add staff payment method fields to users.
-- Apply after migration 014.
USE hospital_management;

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS payment_method ENUM('bank_transfer','mobile_money','paypal','other') NULL AFTER is_active,
    ADD COLUMN IF NOT EXISTS payment_details VARCHAR(255) NULL AFTER payment_method;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_015_staff_payment_method');
