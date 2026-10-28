-- Allow every staff role to document services they personally provide.
-- Patient Portal users are excluded because they receive care rather than provide it.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

SET @seed_staff_patient_service_access := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_005_staff_patient_service_access'
);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE @seed_staff_patient_service_access = 1
  AND r.role_key IN (
      'admin', 'super_admin', 'hospital_admin', 'doctor', 'nurse', 'receptionist',
      'pharmacist', 'laboratory_technician', 'radiologist', 'accountant',
      'store_keeper', 'hr', 'ambulance_driver'
  )
  AND p.permission_key IN ('patient_services.create', 'patient_services.view_own');

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_005_staff_patient_service_access');
