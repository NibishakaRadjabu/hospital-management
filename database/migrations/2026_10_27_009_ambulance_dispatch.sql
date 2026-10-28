-- Add ambulance requests and role-gated transport dispatch.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

SET @seed_ambulance_dispatch := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_009_ambulance_dispatch'
);

INSERT INTO permissions (permission_key, name, description, category) VALUES
    ('ambulance.requests.create', 'Request ambulance transport', 'Create an emergency transport request for a patient.', 'Ambulance')
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),category=VALUES(category);

INSERT IGNORE INTO role_permissions (role_id,permission_id)
SELECT r.id,p.id FROM roles r CROSS JOIN permissions p
WHERE @seed_ambulance_dispatch=1
  AND r.role_key IN ('admin','super_admin','hospital_admin','doctor','nurse','receptionist')
  AND p.permission_key='ambulance.requests.create';

CREATE TABLE IF NOT EXISTS ambulance_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    patient_id BIGINT UNSIGNED NULL,
    requested_by BIGINT UNSIGNED NULL,
    driver_id BIGINT UNSIGNED NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    transport_notes VARCHAR(500) NULL,
    status ENUM('requested', 'assigned', 'en_route', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ambulance_status (status, requested_at),
    CONSTRAINT fk_ambulance_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    CONSTRAINT fk_ambulance_requester FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_ambulance_driver FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_009_ambulance_dispatch');
