-- Add suppliers, simple purchase orders, and equipment maintenance scheduling.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

CREATE TABLE IF NOT EXISTS suppliers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL UNIQUE,
    contact_person VARCHAR(160) NULL,
    phone VARCHAR(40) NULL,
    email VARCHAR(190) NULL,
    address VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS purchase_orders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(40) NOT NULL UNIQUE,
    supplier_id BIGINT UNSIGNED NOT NULL,
    inventory_item_id BIGINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_cost DECIMAL(12,2) NOT NULL,
    status ENUM('ordered', 'received', 'cancelled') NOT NULL DEFAULT 'ordered',
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    received_at DATETIME NULL,
    INDEX idx_purchase_status (status, created_at),
    CONSTRAINT fk_purchase_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    CONSTRAINT fk_purchase_item FOREIGN KEY (inventory_item_id) REFERENCES inventory(id),
    CONSTRAINT fk_purchase_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipment_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_tag VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(160) NOT NULL,
    category VARCHAR(100) NOT NULL,
    location VARCHAR(160) NULL,
    status ENUM('available', 'maintenance', 'retired') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS equipment_maintenance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    scheduled_on DATE NOT NULL,
    maintenance_type VARCHAR(120) NOT NULL,
    details VARCHAR(1000) NULL,
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    recorded_by BIGINT UNSIGNED NOT NULL,
    completed_at DATETIME NULL,
    INDEX idx_maintenance_date (scheduled_on, status),
    CONSTRAINT fk_maintenance_asset FOREIGN KEY (asset_id) REFERENCES equipment_assets(id),
    CONSTRAINT fk_maintenance_user FOREIGN KEY (recorded_by) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_013_procurement_equipment');
