# KivuCare Hospital Management

A PHP 8 and MySQL starter for hospital operations. The current working modules are authentication with database-backed permissions, staff and role administration, department management, audit activity, individual work logs, a role-specific dashboard, patient registration/search/history, and inpatient admissions with ward-bed assignment and discharge.

## Requirements

- PHP 8.0+ with PDO MySQL enabled
- MySQL 5.7+ or MariaDB with InnoDB and generated-column support
- Apache (for the included `.htaccess` protections) or equivalent web-server rules

## Local setup with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. For a **new installation only**, import `database/schema.sql` using phpMyAdmin or the MySQL command line. It creates the `hospital_management` database, tables, role records, the default role-permission matrix, and recommended departments.
3. If your MySQL credentials differ from XAMPP defaults, set `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD` in the Apache/PHP environment before starting Apache. Defaults are `127.0.0.1:3306`, database `hospital_management`, user `root`, and an empty password.
4. Open `http://localhost/hospital%20management/setup.php` and create the first Super Admin / System Owner using a password of at least 12 characters. Setup is blocked once any user account exists; it also seeds a general ward with ten beds.
5. Sign in at `http://localhost/hospital%20management/login.php`.

The project expects to be served from its current folder name. If you rename or move the folder, update the `/hospital%20management` links in the PHP templates and redirects.

## Upgrade an existing installation

## Roles and authorization

For an existing database, **do not re-import `database/schema.sql`**. Back up the database, then run these migrations in order against it from phpMyAdmin or the MySQL command line:

1. `database/migrations/2026_10_27_001_rbac_and_roles.sql`
2. `database/migrations/2026_10_27_002_user_work_logs.sql`
3. `database/migrations/2026_10_27_003_patient_services.sql`
4. `database/migrations/2026_10_27_004_patient_service_access.sql`
5. `database/migrations/2026_10_27_005_staff_patient_service_access.sql`
6. `database/migrations/2026_10_27_006_hospital_departments.sql`
7. `database/migrations/2026_10_27_007_operational_workflows.sql`
8. `database/migrations/2026_10_27_008_discharge_summaries.sql`
9. `database/migrations/2026_10_27_009_ambulance_dispatch.sql`
10. `database/migrations/2026_10_27_010_staff_attendance_payroll.sql`
11. `database/migrations/2026_10_27_011_emergency_intake.sql`
12. `database/migrations/2026_10_27_012_patient_portal_imaging.sql`
13. `database/migrations/2026_10_27_013_procurement_equipment.sql`
14. `database/migrations/2026_10_27_014_hospital_settings_notifications.sql`

Take a database backup before applying migrations. Migrations 001-005 provide roles, work logs, patient-service records, and staff access. Migration 006 adds the expanded hospital department catalogue. Migration 007 adds triage, nursing observations, radiology reports, prescription dispensing records, and triage permissions. Migration 008 adds structured discharge summaries. Migrations 009 and 010 add ambulance dispatch and HR attendance/payroll. Migration 011 allows emergency triage before registration is complete. Migration 012 grants patients access to their own completed imaging reports. Migration 013 adds suppliers, stock-receiving purchase orders, and equipment maintenance schedules. Migration 014 adds the `hospital_settings` table (editable hospital name) and the `notifications` table (per-action unread counts shown in the topbar bell). The migrations do not drop patient or hospital records. Existing accounts are not reassigned: accounts on the old `admin` role keep that role, which receives full access for compatibility. Migrations are tracked in `schema_migrations`; re-running them after successful completion does not restore default permissions intentionally removed by a system owner.

The seeded roles are Super Admin / System Owner, Hospital Administrator, Receptionist, Doctor, Nurse, Laboratory Technician, Pharmacist, Radiologist, Accountant / Cashier, Store Keeper / Inventory Officer, Human Resources, Ambulance Driver, and Patient Portal User. The legacy `admin` role remains available as a full-access compatibility role. System owners have full access; Hospital Administrators can manage staff, departments, schedules, admissions, operations, and billing summaries but cannot configure the system or review detailed financial records. Receptionists handle registration, patient updates, appointments, and admissions. Each staff role can document services it provides and view its own entries; access to other clinical records remains limited by role. Patient Portal permissions are scoped to the linked patient account.

Permissions are stored in `permissions` and `role_permissions`, enforced on the server for each request, and can be reviewed/configured by a system owner from **Roles & permissions**. Hospital Administrators can manage staff accounts, departments, schedules and operational summaries, but do not receive system-configuration, role-management, or detailed financial permissions by default. Patient Portal accounts are linked to one patient record when created.

## Implemented workflows

Working pages cover staff and role administration, departments, audit activity, patient records, appointments and check-in, consultations, emergency triage (including unregistered arrivals), laboratory requests and results, radiology requests and reports, prescriptions and dispensing, inventory adjustments, supplier and purchase order tracking, equipment maintenance schedules, billing and payments, nursing observations, admissions and discharge summaries, ambulance dispatch, HR attendance and payroll, reports, staff work logs, and patient-service records. Every staff role can document services it provides and review its own entries; Hospital Administrators and system owners can review all staff entries. Hospital Administrators receive aggregate billing totals without invoice-level details. Patient Portal users can request appointments, view their own appointments, prescriptions, completed laboratory and imaging results, and invoices, and update their contact details. Portal queries are scoped to the linked patient record.

## Current scope and production note

The seeded department list covers general management, patient reception, clinical specialties, diagnostics, nursing, finance, IT, logistics, facilities, cleaning, and mortuary services. Insurance claims, database backup execution, global system settings, and laundry/mortuary operational records still need dedicated workflows. The appointment screen treats online bookings as requests for confirmation. Invoices currently store a total rather than itemized charges, and HR payroll entries do not calculate taxes or generate payslips. Audit activity is not tamper-resistant. This starter has not been reviewed for real patient data or regulatory compliance. Configure HTTPS, backups, a non-default database account, and appropriate access and audit controls before deployment.
