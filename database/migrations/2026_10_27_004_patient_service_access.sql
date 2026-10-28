-- Restrict patient-care service records to staff with direct care responsibilities.
-- Existing service records are retained; access follows the resulting role grants.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

SET @seed_patient_service_access := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_004_patient_service_access'
);

DELETE rp
FROM role_permissions rp
JOIN roles r ON r.id = rp.role_id
JOIN permissions p ON p.id = rp.permission_id
WHERE @seed_patient_service_access = 1
  AND r.role_key IN ('receptionist', 'accountant', 'store_keeper', 'hr', 'ambulance_driver')
  AND p.permission_key IN ('patient_services.create', 'patient_services.view_own');

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_004_patient_service_access');
