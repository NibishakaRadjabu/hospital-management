-- Allow portal users to view only their own completed imaging reports.
CREATE DATABASE IF NOT EXISTS hospital_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE hospital_management;

SET @seed_patient_portal_imaging := NOT EXISTS (
    SELECT 1 FROM schema_migrations
    WHERE version = '2026_10_27_012_patient_portal_imaging'
);

INSERT INTO permissions (permission_key, name, description, category) VALUES
    ('radiology.results.own_view', 'View own radiology reports', 'View imaging reports linked to the signed-in patient only.', 'Patient Portal')
ON DUPLICATE KEY UPDATE name=VALUES(name),description=VALUES(description),category=VALUES(category);

INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT r.id,p.id FROM roles r CROSS JOIN permissions p
WHERE @seed_patient_portal_imaging=1
  AND r.role_key='patient_portal'
  AND p.permission_key='radiology.results.own_view';

INSERT IGNORE INTO schema_migrations (version)
VALUES ('2026_10_27_012_patient_portal_imaging');
