-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2026 at 06:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `action` varchar(150) NOT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `username`, `action`, `log_time`) VALUES
(3, 'nibishakaradja@gmail.com', 'Added new sale: irembo - 3000', '2026-09-20 06:37:19'),
(4, 'nibishakaradja@gmail.com', 'Added new sale: irembo - 3000 on 2026-09-20', '2026-09-20 06:37:19'),
(5, 'nibishakaradja@gmail.com', 'Added new sale: printing - 300', '2026-09-20 06:38:03'),
(6, 'nibishakaradja@gmail.com', 'Added new sale: printing - 300 on 2026-09-20', '2026-09-20 06:38:03'),
(3, 'nibishakaradja@gmail.com', 'Added new sale: irembo - 3000', '2026-09-20 06:37:19'),
(4, 'nibishakaradja@gmail.com', 'Added new sale: irembo - 3000 on 2026-09-20', '2026-09-20 06:37:19'),
(5, 'nibishakaradja@gmail.com', 'Added new sale: printing - 300', '2026-09-20 06:38:03'),
(6, 'nibishakaradja@gmail.com', 'Added new sale: printing - 300 on 2026-09-20', '2026-09-20 06:38:03');

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `bed_id` int(10) UNSIGNED NOT NULL,
  `admitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `admitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `discharged_at` datetime DEFAULT NULL,
  `reason` text NOT NULL,
  `status` enum('admitted','discharged') NOT NULL DEFAULT 'admitted',
  `active_patient_id` bigint(20) UNSIGNED GENERATED ALWAYS AS (case when `status` = 'admitted' then `patient_id` else NULL end) STORED,
  `active_bed_id` int(10) UNSIGNED GENERATED ALWAYS AS (case when `status` = 'admitted' then `bed_id` else NULL end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `patient_id`, `bed_id`, `admitted_by`, `admitted_at`, `discharged_at`, `reason`, `status`) VALUES
(1, 1, 1, 2, '2026-10-27 11:06:42', NULL, 'bad condition', 'admitted');

-- --------------------------------------------------------

--
-- Table structure for table `ambulance_requests`
--

CREATE TABLE `ambulance_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `driver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `transport_notes` varchar(500) DEFAULT NULL,
  `status` enum('requested','assigned','en_route','completed','cancelled') NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ambulance_requests`
--

INSERT INTO `ambulance_requests` (`id`, `patient_id`, `requested_by`, `driver_id`, `pickup_location`, `destination`, `transport_notes`, `status`, `requested_at`, `updated_at`) VALUES
(1, NULL, 2, NULL, 'rubavu', 'kgl', '0789722814', 'requested', '2026-10-30 08:39:02', '2026-10-30 08:39:02');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `scheduled_at` datetime NOT NULL,
  `status` enum('scheduled','checked_in','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `scheduled_at`, `status`, `reason`, `created_at`) VALUES
(1, 2, 1, '2026-10-30 11:39:00', 'checked_in', 'check', '2026-10-30 09:40:30');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `actor_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `entity_type` varchar(80) NOT NULL,
  `entity_id` varchar(80) DEFAULT NULL,
  `details` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 4, 'create', 'work_log', '1', 'Work log entry recorded', '::1', '2026-10-27 09:57:34'),
(2, 4, 'create', 'patient_service', '1', 'Patient service recorded', '::1', '2026-10-27 10:04:20'),
(3, 4, 'login', 'user', '4', 'Successful sign-in', '::1', '2026-10-27 12:02:26'),
(4, 2, 'login', 'user', '2', 'Successful sign-in', '::1', '2026-10-27 13:24:17'),
(5, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-27 13:27:41'),
(6, 1, 'create', 'invoice', '1', 'Invoice generated', '::1', '2026-10-27 13:29:02'),
(7, 1, 'payment', 'invoice', '1', 'Payment received', '::1', '2026-10-27 13:29:25'),
(8, 2, 'login', 'user', '2', 'Successful sign-in', '::1', '2026-10-28 15:32:33'),
(9, 4, 'login', 'user', '4', 'Successful sign-in', '::1', '2026-10-28 15:33:34'),
(10, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-28 15:50:01'),
(11, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-28 16:09:52'),
(12, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-29 13:35:37'),
(13, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 06:53:45'),
(14, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 07:06:52'),
(15, 1, 'create', 'user', '5', 'Staff account created', '::1', '2026-10-30 07:09:48'),
(16, 5, 'login', 'user', '5', 'Successful sign-in', '::1', '2026-10-30 07:10:16'),
(17, 5, 'record', 'staff_attendance', '4', 'Attendance recorded for 2026-10-30', '::1', '2026-10-30 07:16:57'),
(18, 2, 'login', 'user', '2', 'Successful sign-in', '::1', '2026-10-30 07:18:10'),
(19, 2, 'login', 'user', '2', 'Successful sign-in', '::1', '2026-10-30 07:21:50'),
(20, 2, 'create', 'patient', '2', 'Patient registered', '::1', '2026-10-30 07:23:02'),
(21, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 07:23:30'),
(22, 1, 'update', 'hospital_settings', 'hospital_name', 'Hospital name changed to: congo healthy', '::1', '2026-10-30 08:16:34'),
(23, 2, 'login', 'user', '2', 'Successful sign-in', '::1', '2026-10-30 08:21:24'),
(24, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 08:24:52'),
(25, 3, 'login', 'user', '3', 'Successful sign-in', '::1', '2026-10-30 08:30:20'),
(26, 3, 'update', 'user', '3', 'Payment method updated', '::1', '2026-10-30 08:31:27'),
(27, 3, 'login', 'user', '3', 'Successful sign-in', '::1', '2026-10-30 08:31:44'),
(28, 5, 'login', 'user', '5', 'Successful sign-in', '::1', '2026-10-30 08:31:54'),
(29, 2, 'login', 'user', '2', 'Successful sign-in', '::1', '2026-10-30 08:32:46'),
(30, 2, 'create', 'ambulance_request', '1', 'Patient transport requested', '::1', '2026-10-30 08:39:02'),
(31, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 08:39:38'),
(32, 1, 'create', 'user', '6', 'Staff account created', '::1', '2026-10-30 08:41:38'),
(33, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 08:44:36'),
(34, 1, 'create', 'user', '7', 'Staff account created', '::1', '2026-10-30 08:56:11'),
(35, 7, 'login', 'user', '7', 'Successful sign-in', '::1', '2026-10-30 08:56:36'),
(36, 7, 'login', 'user', '7', 'Successful sign-in', '::1', '2026-10-30 08:57:24'),
(37, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 08:57:43'),
(38, 1, 'create', 'user', '8', 'Patient portal account created', '::1', '2026-10-30 09:01:03'),
(39, 8, 'login', 'user', '8', 'Successful sign-in', '::1', '2026-10-30 09:01:54'),
(40, 5, 'login', 'user', '5', 'Successful sign-in', '::1', '2026-10-30 09:34:42'),
(41, 8, 'login', 'user', '8', 'Successful sign-in', '::1', '2026-10-30 09:38:46'),
(42, 8, 'self_book', 'appointment', '1', 'Patient portal appointment request', '::1', '2026-10-30 09:40:30'),
(43, 1, 'login', 'user', '1', 'Successful sign-in', '::1', '2026-10-30 09:41:13'),
(44, 1, 'update', 'appointment', '1', 'Appointment status changed to checked_in', '::1', '2026-10-30 09:41:46'),
(45, 1, 'create', 'conference_room', '1', 'chat room created: conference', '::1', '2026-10-30 10:19:24'),
(46, 1, 'create', 'conference_room', '2', 'video room created: video conference', '::1', '2026-10-30 10:20:20'),
(47, 3, 'login', 'user', '3', 'Successful sign-in', '::1', '2026-10-30 10:23:46'),
(48, 1, 'close', 'conference_room', '2', 'Conference room closed', '::1', '2026-10-30 10:25:29'),
(49, 1, 'create', 'conference_room', '3', 'video room created: video conference', '::1', '2026-10-30 10:25:54'),
(50, 1, 'close', 'conference_room', '3', 'Conference room closed', '::1', '2026-10-30 10:27:22'),
(51, 4, 'login', 'user', '4', 'Successful sign-in', '::1', '2026-10-30 16:48:54'),
(52, 5, 'login', 'user', '5', 'Successful sign-in', '::1', '2026-10-30 16:56:27');

-- --------------------------------------------------------

--
-- Table structure for table `beds`
--

CREATE TABLE `beds` (
  `id` int(10) UNSIGNED NOT NULL,
  `ward_id` int(10) UNSIGNED NOT NULL,
  `bed_number` varchar(30) NOT NULL,
  `status` enum('available','occupied','maintenance') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `beds`
--

INSERT INTO `beds` (`id`, `ward_id`, `bed_number`, `status`) VALUES
(1, 1, 'GW-01', 'occupied'),
(2, 1, 'GW-02', 'available'),
(3, 1, 'GW-03', 'available'),
(4, 1, 'GW-04', 'available'),
(5, 1, 'GW-05', 'available'),
(6, 1, 'GW-06', 'available'),
(7, 1, 'GW-07', 'available'),
(8, 1, 'GW-08', 'available'),
(9, 1, 'GW-09', 'available'),
(10, 1, 'GW-10', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `conference_messages`
--

CREATE TABLE `conference_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conference_messages`
--

INSERT INTO `conference_messages` (`id`, `room_id`, `user_id`, `message`, `sent_at`) VALUES
(1, 1, 1, 'hi', '2026-10-30 10:19:34');

-- --------------------------------------------------------

--
-- Table structure for table `conference_participants`
--

CREATE TABLE `conference_participants` (
  `room_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conference_participants`
--

INSERT INTO `conference_participants` (`room_id`, `user_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `conference_rooms`
--

CREATE TABLE `conference_rooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(160) NOT NULL,
  `type` enum('chat','video') NOT NULL DEFAULT 'chat',
  `room_code` varchar(80) NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `conference_rooms`
--

INSERT INTO `conference_rooms` (`id`, `title`, `type`, `room_code`, `created_by`, `is_active`, `created_at`) VALUES
(1, 'conference', 'chat', '539e84f92e2ba8f0', 1, 1, '2026-10-30 10:19:24'),
(2, 'video conference', 'video', 'aaf10e0ec152d44c', 1, 0, '2026-10-30 10:20:20'),
(3, 'video conference', 'video', 'ac9f0685aa3ed0c6', 1, 0, '2026-10-30 10:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`id`, `name`) VALUES
(2, 'Audio Studio'),
(3, 'IT & Irembo'),
(4, 'photography & videography'),
(2, 'Audio Studio'),
(3, 'IT & Irembo'),
(4, 'photography & videography');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`) VALUES
(1, 'General Medicine', 'General outpatient services'),
(2, 'Reception', 'Patient registration and front desk'),
(3, 'OPD', 'Outpatient consultations'),
(4, 'Emergency', 'Emergency and urgent care'),
(5, 'Admission', 'Inpatient admission services'),
(6, 'Wards', 'Inpatient ward services'),
(7, 'Laboratory', 'Laboratory testing and results'),
(8, 'Radiology', 'Imaging and radiology services'),
(9, 'Pharmacy', 'Medicine dispensing and stock'),
(10, 'Surgery / Theatre', 'Surgical and theatre services'),
(11, 'Billing', 'Patient billing and payments'),
(12, 'Inventory', 'Supplies and equipment'),
(13, 'Human Resources', 'Staff and payroll'),
(14, 'Ambulance', 'Emergency patient transport');

-- --------------------------------------------------------

--
-- Table structure for table `discharge_summaries`
--

CREATE TABLE `discharge_summaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admission_id` bigint(20) UNSIGNED NOT NULL,
  `written_by` bigint(20) UNSIGNED DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `summary` text NOT NULL,
  `medication_plan` text DEFAULT NULL,
  `follow_up` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` int(10) UNSIGNED DEFAULT NULL,
  `license_number` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `department_id`, `license_number`) VALUES
(1, 4, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `emergency_triage`
--

CREATE TABLE `emergency_triage` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED DEFAULT NULL,
  `patient_name` varchar(160) DEFAULT NULL,
  `assessed_by` bigint(20) UNSIGNED NOT NULL,
  `acuity` enum('resuscitation','emergency','urgent','less_urgent','non_urgent') NOT NULL,
  `chief_complaint` varchar(500) NOT NULL,
  `temperature_c` decimal(4,1) DEFAULT NULL,
  `pulse_bpm` smallint(5) UNSIGNED DEFAULT NULL,
  `respiratory_rate` smallint(5) UNSIGNED DEFAULT NULL,
  `systolic_bp` smallint(5) UNSIGNED DEFAULT NULL,
  `diastolic_bp` smallint(5) UNSIGNED DEFAULT NULL,
  `oxygen_saturation` tinyint(3) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `assessed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_assets`
--

CREATE TABLE `equipment_assets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_tag` varchar(80) NOT NULL,
  `name` varchar(160) NOT NULL,
  `category` varchar(100) NOT NULL,
  `location` varchar(160) DEFAULT NULL,
  `status` enum('available','maintenance','retired') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_maintenance`
--

CREATE TABLE `equipment_maintenance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `asset_id` bigint(20) UNSIGNED NOT NULL,
  `scheduled_on` date NOT NULL,
  `maintenance_type` varchar(120) NOT NULL,
  `details` varchar(1000) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `recorded_by` bigint(20) UNSIGNED NOT NULL,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `description` varchar(150) NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `expense_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hospital_settings`
--

CREATE TABLE `hospital_settings` (
  `setting_key` varchar(80) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hospital_settings`
--

INSERT INTO `hospital_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('hospital_name', 'congo healthy', '2026-10-30 08:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(160) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reorder_level` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `unit` varchar(40) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_no` varchar(40) NOT NULL,
  `status` enum('unpaid','partial','paid','void') NOT NULL DEFAULT 'unpaid',
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `patient_id`, `invoice_no`, `status`, `total_amount`, `created_at`) VALUES
(1, 1, 'INV-261027-7F0768', 'partial', 200000.00, '2026-10-27 13:29:02');

-- --------------------------------------------------------

--
-- Table structure for table `lab_results`
--

CREATE TABLE `lab_results` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `lab_test_id` bigint(20) UNSIGNED NOT NULL,
  `result` text NOT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lab_tests`
--

CREATE TABLE `lab_tests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `test_name` varchar(160) NOT NULL,
  `status` enum('requested','in_progress','completed','cancelled') NOT NULL DEFAULT 'requested',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `unit` varchar(40) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `reorder_level` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `expires_on` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(80) NOT NULL,
  `entity_type` varchar(80) NOT NULL,
  `entity_id` varchar(80) DEFAULT NULL,
  `message` varchar(500) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'patient_registered', 'patient', '2', 'receptionist registered new patient PT-261030-5EAB2BDFB5.', 1, '2026-10-30 07:23:02'),
(2, 3, 'patient_registered', 'patient', '2', 'receptionist registered new patient PT-261030-5EAB2BDFB5.', 0, '2026-10-30 07:23:02'),
(3, 4, 'patient_registered', 'patient', '2', 'receptionist registered new patient PT-261030-5EAB2BDFB5.', 0, '2026-10-30 07:23:02'),
(4, 5, 'patient_registered', 'patient', '2', 'receptionist registered new patient PT-261030-5EAB2BDFB5.', 1, '2026-10-30 07:23:02'),
(5, 1, 'ambulance_requested', 'ambulance_request', '1', 'receptionist requested ambulance transport.', 1, '2026-10-30 08:39:02'),
(6, 3, 'ambulance_requested', 'ambulance_request', '1', 'receptionist requested ambulance transport.', 0, '2026-10-30 08:39:02'),
(7, 4, 'ambulance_requested', 'ambulance_request', '1', 'receptionist requested ambulance transport.', 0, '2026-10-30 08:39:02'),
(8, 5, 'ambulance_requested', 'ambulance_request', '1', 'receptionist requested ambulance transport.', 0, '2026-10-30 08:39:02'),
(9, 2, 'appointment_updated', 'appointment', '1', 'Nibishaka Radjab updated an appointment status to checked in.', 0, '2026-10-30 09:41:46'),
(10, 3, 'appointment_updated', 'appointment', '1', 'Nibishaka Radjab updated an appointment status to checked in.', 0, '2026-10-30 09:41:46'),
(11, 4, 'appointment_updated', 'appointment', '1', 'Nibishaka Radjab updated an appointment status to checked in.', 0, '2026-10-30 09:41:46'),
(12, 5, 'appointment_updated', 'appointment', '1', 'Nibishaka Radjab updated an appointment status to checked in.', 0, '2026-10-30 09:41:46'),
(13, 6, 'appointment_updated', 'appointment', '1', 'Nibishaka Radjab updated an appointment status to checked in.', 0, '2026-10-30 09:41:46'),
(14, 7, 'appointment_updated', 'appointment', '1', 'Nibishaka Radjab updated an appointment status to checked in.', 0, '2026-10-30 09:41:46'),
(15, 2, 'conference_invite', 'conference_room', '1', 'Nibishaka Radjab invited you to a chat conference: conference', 0, '2026-10-30 10:19:24'),
(16, 3, 'conference_invite', 'conference_room', '1', 'Nibishaka Radjab invited you to a chat conference: conference', 1, '2026-10-30 10:19:24'),
(17, 4, 'conference_invite', 'conference_room', '1', 'Nibishaka Radjab invited you to a chat conference: conference', 1, '2026-10-30 10:19:24'),
(18, 5, 'conference_invite', 'conference_room', '1', 'Nibishaka Radjab invited you to a chat conference: conference', 0, '2026-10-30 10:19:24'),
(19, 6, 'conference_invite', 'conference_room', '1', 'Nibishaka Radjab invited you to a chat conference: conference', 0, '2026-10-30 10:19:24'),
(20, 7, 'conference_invite', 'conference_room', '1', 'Nibishaka Radjab invited you to a chat conference: conference', 0, '2026-10-30 10:19:24'),
(21, 2, 'conference_invite', 'conference_room', '2', 'Nibishaka Radjab invited you to a video conference: video conference', 0, '2026-10-30 10:20:20'),
(22, 3, 'conference_invite', 'conference_room', '2', 'Nibishaka Radjab invited you to a video conference: video conference', 1, '2026-10-30 10:20:20'),
(23, 4, 'conference_invite', 'conference_room', '2', 'Nibishaka Radjab invited you to a video conference: video conference', 1, '2026-10-30 10:20:20'),
(24, 5, 'conference_invite', 'conference_room', '2', 'Nibishaka Radjab invited you to a video conference: video conference', 0, '2026-10-30 10:20:20'),
(25, 6, 'conference_invite', 'conference_room', '2', 'Nibishaka Radjab invited you to a video conference: video conference', 0, '2026-10-30 10:20:20'),
(26, 7, 'conference_invite', 'conference_room', '2', 'Nibishaka Radjab invited you to a video conference: video conference', 0, '2026-10-30 10:20:20');

-- --------------------------------------------------------

--
-- Table structure for table `nursing_notes`
--

CREATE TABLE `nursing_notes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `recorded_by` bigint(20) UNSIGNED NOT NULL,
  `note_type` enum('observation','vital_signs','medication_administered','care') NOT NULL,
  `note` text NOT NULL,
  `temperature_c` decimal(4,1) DEFAULT NULL,
  `pulse_bpm` smallint(5) UNSIGNED DEFAULT NULL,
  `respiratory_rate` smallint(5) UNSIGNED DEFAULT NULL,
  `systolic_bp` smallint(5) UNSIGNED DEFAULT NULL,
  `diastolic_bp` smallint(5) UNSIGNED DEFAULT NULL,
  `oxygen_saturation` tinyint(3) UNSIGNED DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_no` varchar(30) NOT NULL,
  `first_name` varchar(80) NOT NULL,
  `last_name` varchar(80) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `sex` enum('female','male','other','unspecified') NOT NULL DEFAULT 'unspecified',
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `emergency_contact_name` varchar(160) DEFAULT NULL,
  `emergency_contact_phone` varchar(40) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `patient_no`, `first_name`, `last_name`, `date_of_birth`, `sex`, `phone`, `email`, `address`, `blood_group`, `emergency_contact_name`, `emergency_contact_phone`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PT-261027-A989AF7330', 'jean', 'Egide', '2001-10-27', 'male', '0789722814', 'basag@gmail.com', '250 Rwanda, Rubavu', NULL, 'Nibishaka', '0789722814', '001', 2, '2026-10-27 09:05:48', '2026-10-27 09:05:48'),
(2, 'PT-261030-5EAB2BDFB5', 'uwimana', 'Damascene', '1999-03-30', 'male', '0738910183', 'admin@gmail.com', '250 Rwanda, Rubavu', 'A+', 'Nibishaka', '0789722814', 'gnlnmgrmg', 2, '2026-10-30 07:23:02', '2026-10-30 07:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `patient_portal_accounts`
--

CREATE TABLE `patient_portal_accounts` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_portal_accounts`
--

INSERT INTO `patient_portal_accounts` (`user_id`, `patient_id`, `created_at`) VALUES
(8, 2, '2026-10-30 09:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `patient_services`
--

CREATE TABLE `patient_services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `provided_by` bigint(20) UNSIGNED NOT NULL,
  `service_date` date NOT NULL,
  `category` varchar(40) NOT NULL,
  `service_name` varchar(160) NOT NULL,
  `details` text NOT NULL,
  `duration_minutes` smallint(5) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_services`
--

INSERT INTO `patient_services` (`id`, `patient_id`, `provided_by`, `service_date`, `category`, `service_name`, `details`, `duration_minutes`, `created_at`) VALUES
(1, 1, 4, '2026-10-27', 'consultation', 'testing', 'hcjm,nfvm,ncvbhcm,xcvnm.nzc.,vnjlfkbfxcn.v,xc', 40, '2026-10-27 10:04:20');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `method` enum('cash','mobile_money','card','insurance','bank_transfer','other') NOT NULL DEFAULT 'cash',
  `reference` varchar(120) DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `amount`, `method`, `reference`, `received_by`, `paid_at`) VALUES
(1, 1, 100000.00, 'mobile_money', '0789722814', 1, '2026-10-27 13:29:25');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_entries`
--

CREATE TABLE `payroll_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pay_period` date NOT NULL,
  `gross_amount` decimal(12,2) NOT NULL,
  `deductions` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL,
  `status` enum('prepared','paid') NOT NULL DEFAULT 'prepared',
  `recorded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `permission_key` varchar(100) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(255) NOT NULL,
  `category` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_key`, `name`, `description`, `category`) VALUES
(1, 'dashboard.view', 'View dashboard', 'Open the role-specific hospital dashboard.', 'System'),
(2, 'users.view', 'View staff accounts', 'View staff account records.', 'Administration'),
(3, 'users.manage', 'Manage staff accounts', 'Create and deactivate staff accounts.', 'Administration'),
(4, 'roles.manage', 'Manage roles and permissions', 'Create roles and configure role permissions.', 'System'),
(5, 'settings.manage', 'Manage system settings', 'Change global system configuration.', 'System'),
(6, 'database.backup', 'Back up database', 'Create and download database backups.', 'System'),
(7, 'audit.view', 'View audit activity', 'Review the system audit trail.', 'System'),
(8, 'departments.view', 'View departments', 'View hospital departments.', 'Administration'),
(9, 'departments.manage', 'Manage departments', 'Create and update hospital departments.', 'Administration'),
(10, 'reports.view', 'View hospital reports', 'View operational hospital reports.', 'Reports'),
(11, 'billing.overview', 'View billing overview', 'View high-level billing and revenue summaries.', 'Billing'),
(12, 'patients.view', 'View patient information', 'View patient identity and contact information.', 'Patients'),
(13, 'patients.create', 'Register patients', 'Register new patients.', 'Patients'),
(14, 'patients.update', 'Update patient information', 'Edit patient identity and contact information.', 'Patients'),
(15, 'patients.history', 'View medical history', 'View clinical visit and admission history.', 'Medical'),
(16, 'appointments.view', 'View appointments', 'View appointment schedules and statuses.', 'Appointments'),
(17, 'appointments.manage', 'Manage appointments', 'Book, update, and cancel appointments.', 'Appointments'),
(18, 'appointments.schedule', 'Manage doctor schedules', 'Configure doctor schedules.', 'Appointments'),
(19, 'appointments.self_book', 'Book own appointments', 'Book appointments for the signed-in patient only.', 'Patient Portal'),
(20, 'visits.view', 'View consultations', 'View consultation records.', 'Medical'),
(21, 'visits.manage', 'Manage consultations', 'Create diagnoses and medical notes.', 'Medical'),
(22, 'prescriptions.view', 'View prescriptions', 'View prescription records for care.', 'Medical'),
(23, 'prescriptions.create', 'Create prescriptions', 'Prescribe medicines for a patient.', 'Medical'),
(24, 'prescriptions.own_view', 'View own prescriptions', 'View prescriptions linked to the signed-in patient only.', 'Patient Portal'),
(25, 'lab.requests.view', 'View laboratory requests', 'View laboratory test requests.', 'Laboratory'),
(26, 'lab.requests.create', 'Request laboratory tests', 'Create laboratory test requests.', 'Laboratory'),
(27, 'lab.results.view', 'View laboratory results', 'View completed laboratory results.', 'Laboratory'),
(28, 'lab.results.manage', 'Record laboratory results', 'Enter and update laboratory results.', 'Laboratory'),
(29, 'lab.results.own_view', 'View own laboratory results', 'View results linked to the signed-in patient only.', 'Patient Portal'),
(30, 'radiology.requests.view', 'View radiology requests', 'View radiology scan requests.', 'Radiology'),
(31, 'radiology.requests.create', 'Request radiology scans', 'Create radiology scan requests.', 'Radiology'),
(32, 'radiology.results.manage', 'Record radiology results', 'Upload scan results and radiology reports.', 'Radiology'),
(33, 'admissions.view', 'View admissions', 'View inpatient admission and ward placement records.', 'Admissions'),
(34, 'admissions.create', 'Admit patients', 'Create patient admissions and assign beds.', 'Admissions'),
(35, 'admissions.discharge', 'Discharge patients', 'Discharge patients and release beds.', 'Admissions'),
(36, 'wards.view', 'View ward records', 'View wards, beds, and inpatient census.', 'Wards'),
(37, 'wards.manage', 'Manage wards', 'Manage ward and bed availability.', 'Wards'),
(38, 'nursing_notes.manage', 'Manage nursing notes and vitals', 'Record vital signs, nursing notes, and administered medicines.', 'Wards'),
(39, 'medicines.view', 'View medicine stock', 'View the medicine catalogue and stock levels.', 'Pharmacy'),
(40, 'medicines.manage', 'Manage medicine inventory', 'Manage medicines, stock levels, and expiries.', 'Pharmacy'),
(41, 'pharmacy.dispense', 'Dispense medicines', 'Dispense medicines against prescriptions.', 'Pharmacy'),
(42, 'pharmacy.reports', 'View pharmacy reports', 'Review dispensing and medicine stock reports.', 'Pharmacy'),
(43, 'billing.view', 'View financial records', 'View invoices and financial records.', 'Billing'),
(44, 'invoices.manage', 'Manage invoices', 'Create and update patient invoices.', 'Billing'),
(45, 'payments.manage', 'Receive payments', 'Record payments and insurance claims.', 'Billing'),
(46, 'receipts.issue', 'Issue payment receipts', 'Issue payment receipts.', 'Billing'),
(47, 'financial_reports.view', 'View financial reports', 'View detailed revenue and financial reports.', 'Billing'),
(48, 'inventory.view', 'View inventory', 'View equipment and general inventory stock.', 'Inventory'),
(49, 'inventory.manage', 'Manage inventory', 'Manage stock and equipment records.', 'Inventory'),
(50, 'suppliers.manage', 'Manage suppliers', 'Create and update supplier records.', 'Inventory'),
(51, 'purchase_orders.manage', 'Manage purchase orders', 'Create and update inventory purchase orders.', 'Inventory'),
(52, 'inventory.reports', 'View inventory reports', 'Review stock, purchases, and reorder reports.', 'Inventory'),
(53, 'employees.manage', 'Manage employees', 'Manage staff employee records and recruitment.', 'Human Resources'),
(54, 'payroll.manage', 'Manage payroll', 'Manage staff payroll records.', 'Human Resources'),
(55, 'attendance.manage', 'Manage attendance and leave', 'Manage staff attendance and leave records.', 'Human Resources'),
(56, 'ambulance.manage', 'Manage ambulance transport', 'View and manage ambulance requests and transport records.', 'Ambulance'),
(57, 'emergency_requests.view', 'View emergency requests', 'View emergency transport requests.', 'Ambulance'),
(58, 'invoices.own_view', 'View own invoices', 'View invoices linked to the signed-in patient only.', 'Patient Portal'),
(59, 'profile.own_update', 'Update own profile', 'Update the signed-in patient portal profile only.', 'Patient Portal'),
(60, 'work_logs.create', 'Record own work', 'Create a work log entry for the signed-in user.', 'Work Log'),
(61, 'work_logs.view_own', 'View own work log', 'View work log entries created by the signed-in user.', 'Work Log'),
(62, 'work_logs.view_all', 'View all work logs', 'Review work log entries created by any user.', 'Work Log'),
(63, 'patient_services.create', 'Record patient services', 'Record a service personally provided to a patient.', 'Patient Services'),
(64, 'patient_services.view_own', 'View own patient services', 'View service records created by the signed-in user.', 'Patient Services'),
(65, 'patient_services.view_all', 'View all patient services', 'Review service records created by any staff member.', 'Patient Services'),
(69, 'triage.view', 'View emergency triage', 'View emergency arrivals and triage assessments.', 'Emergency'),
(70, 'triage.manage', 'Assess emergency patients', 'Record triage acuity, symptoms, and initial vital signs.', 'Emergency'),
(71, 'ambulance.requests.create', 'Request ambulance transport', 'Create an emergency transport request for a patient.', 'Ambulance'),
(72, 'radiology.results.own_view', 'View own radiology reports', 'View imaging reports linked to the signed-in patient only.', 'Patient Portal');

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `visit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription_dispensations`
--

CREATE TABLE `prescription_dispensations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prescription_item_id` bigint(20) UNSIGNED NOT NULL,
  `dispensed_by` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `dispensed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescription_items`
--

CREATE TABLE `prescription_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prescription_id` bigint(20) UNSIGNED NOT NULL,
  `medicine_id` bigint(20) UNSIGNED NOT NULL,
  `dosage` varchar(120) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `instructions` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(40) NOT NULL,
  `supplier_id` bigint(20) UNSIGNED NOT NULL,
  `inventory_item_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `status` enum('ordered','received','cancelled') NOT NULL DEFAULT 'ordered',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `received_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `radiology_requests`
--

CREATE TABLE `radiology_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `scan_name` varchar(160) NOT NULL,
  `clinical_notes` text DEFAULT NULL,
  `status` enum('requested','in_progress','completed','cancelled') NOT NULL DEFAULT 'requested',
  `report` text DEFAULT NULL,
  `performed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports_log`
--

CREATE TABLE `reports_log` (
  `id` int(11) NOT NULL,
  `report_type` enum('daily','weekly','monthly') NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `role_key` varchar(40) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_key`, `name`) VALUES
(1, 'admin', 'Legacy System Administrator'),
(2, 'doctor', 'Doctor'),
(3, 'nurse', 'Nurse'),
(4, 'receptionist', 'Receptionist'),
(5, 'pharmacist', 'Pharmacist'),
(6, 'laboratory_technician', 'Laboratory Technician'),
(7, 'accountant', 'Accountant / Cashier'),
(8, 'super_admin', 'Super Admin / System Owner'),
(9, 'hospital_admin', 'Hospital Administrator'),
(10, 'radiologist', 'Radiologist'),
(11, 'store_keeper', 'Store Keeper / Inventory Officer'),
(12, 'hr', 'Human Resources'),
(13, 'ambulance_driver', 'Ambulance Driver'),
(14, 'patient_portal', 'Patient Portal User');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `permission_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50),
(1, 51),
(1, 52),
(1, 53),
(1, 54),
(1, 55),
(1, 56),
(1, 57),
(1, 58),
(1, 59),
(1, 60),
(1, 61),
(1, 62),
(1, 63),
(1, 64),
(1, 65),
(1, 69),
(1, 70),
(1, 71),
(2, 1),
(2, 12),
(2, 15),
(2, 16),
(2, 17),
(2, 20),
(2, 21),
(2, 22),
(2, 23),
(2, 25),
(2, 26),
(2, 27),
(2, 30),
(2, 31),
(2, 33),
(2, 34),
(2, 35),
(2, 36),
(2, 39),
(2, 60),
(2, 61),
(2, 63),
(2, 64),
(2, 69),
(2, 70),
(2, 71),
(3, 1),
(3, 12),
(3, 33),
(3, 36),
(3, 37),
(3, 38),
(3, 60),
(3, 61),
(3, 63),
(3, 64),
(3, 69),
(3, 70),
(3, 71),
(4, 1),
(4, 12),
(4, 13),
(4, 14),
(4, 16),
(4, 17),
(4, 33),
(4, 34),
(4, 60),
(4, 61),
(4, 63),
(4, 64),
(4, 71),
(5, 1),
(5, 22),
(5, 39),
(5, 40),
(5, 41),
(5, 42),
(5, 60),
(5, 61),
(5, 63),
(5, 64),
(6, 1),
(6, 12),
(6, 25),
(6, 27),
(6, 28),
(6, 60),
(6, 61),
(6, 63),
(6, 64),
(7, 1),
(7, 43),
(7, 44),
(7, 45),
(7, 46),
(7, 47),
(7, 60),
(7, 61),
(7, 63),
(7, 64),
(8, 1),
(8, 2),
(8, 3),
(8, 4),
(8, 5),
(8, 6),
(8, 7),
(8, 8),
(8, 9),
(8, 10),
(8, 11),
(8, 12),
(8, 13),
(8, 14),
(8, 15),
(8, 16),
(8, 17),
(8, 18),
(8, 19),
(8, 20),
(8, 21),
(8, 22),
(8, 23),
(8, 24),
(8, 25),
(8, 26),
(8, 27),
(8, 28),
(8, 29),
(8, 30),
(8, 31),
(8, 32),
(8, 33),
(8, 34),
(8, 35),
(8, 36),
(8, 37),
(8, 38),
(8, 39),
(8, 40),
(8, 41),
(8, 42),
(8, 43),
(8, 44),
(8, 45),
(8, 46),
(8, 47),
(8, 48),
(8, 49),
(8, 50),
(8, 51),
(8, 52),
(8, 53),
(8, 54),
(8, 55),
(8, 56),
(8, 57),
(8, 58),
(8, 59),
(8, 60),
(8, 61),
(8, 62),
(8, 63),
(8, 64),
(8, 65),
(8, 69),
(8, 70),
(8, 71),
(9, 1),
(9, 2),
(9, 3),
(9, 8),
(9, 9),
(9, 10),
(9, 11),
(9, 12),
(9, 13),
(9, 14),
(9, 16),
(9, 17),
(9, 18),
(9, 33),
(9, 34),
(9, 35),
(9, 36),
(9, 37),
(9, 39),
(9, 48),
(9, 60),
(9, 61),
(9, 62),
(9, 63),
(9, 64),
(9, 65),
(9, 69),
(9, 71),
(10, 1),
(10, 30),
(10, 32),
(10, 60),
(10, 61),
(10, 63),
(10, 64),
(11, 1),
(11, 48),
(11, 49),
(11, 50),
(11, 51),
(11, 52),
(11, 60),
(11, 61),
(11, 63),
(11, 64),
(12, 1),
(12, 53),
(12, 54),
(12, 55),
(12, 60),
(12, 61),
(12, 63),
(12, 64),
(13, 1),
(13, 56),
(13, 57),
(13, 60),
(13, 61),
(13, 63),
(13, 64),
(14, 1),
(14, 19),
(14, 24),
(14, 29),
(14, 58),
(14, 59),
(14, 72);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product`, `amount`, `transaction_date`) VALUES
(6, 'printing', 100.00, '2026-09-20'),
(6, 'printing', 100.00, '2026-09-20');

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `version` varchar(120) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schema_migrations`
--

INSERT INTO `schema_migrations` (`version`, `applied_at`) VALUES
('2026_10_27_001_rbac_and_roles', '2026-10-27 09:33:17'),
('2026_10_27_002_user_work_logs', '2026-10-27 09:38:52'),
('2026_10_27_003_patient_services', '2026-10-27 09:46:58'),
('2026_10_27_004_patient_service_access', '2026-10-27 09:53:08'),
('2026_10_27_005_staff_patient_service_access', '2026-10-27 09:56:14'),
('2026_10_27_007_operational_workflows', '2026-10-27 11:47:30'),
('2026_10_27_008_discharge_summaries', '2026-10-27 11:47:45'),
('2026_10_27_009_ambulance_dispatch', '2026-10-27 12:01:24'),
('2026_10_27_010_staff_attendance_payroll', '2026-10-27 12:01:36'),
('2026_10_27_011_emergency_intake', '2026-10-27 12:01:47'),
('2026_10_27_012_patient_portal_imaging', '2026-10-27 12:02:00'),
('2026_10_27_013_procurement_equipment', '2026-10-27 12:02:13'),
('2026_10_27_014_hospital_settings_notifications', '2026-10-30 07:13:07'),
('2026_10_27_015_staff_payment_method', '2026-10-30 07:58:50'),
('2026_10_27_016_conference', '2026-10-30 10:18:41');

-- --------------------------------------------------------

--
-- Table structure for table `staff_attendance`
--

CREATE TABLE `staff_attendance` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `work_date` date NOT NULL,
  `status` enum('present','absent','leave') NOT NULL,
  `clock_in` time DEFAULT NULL,
  `clock_out` time DEFAULT NULL,
  `note` varchar(500) DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff_attendance`
--

INSERT INTO `staff_attendance` (`id`, `user_id`, `work_date`, `status`, `clock_in`, `clock_out`, `note`, `recorded_by`, `created_at`) VALUES
(1, 4, '2026-10-30', 'present', '09:16:00', NULL, NULL, 5, '2026-10-30 07:16:57');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `contact_person` varchar(160) DEFAULT NULL,
  `phone` varchar(40) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` tinyint(3) UNSIGNED NOT NULL,
  `full_name` varchar(160) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `payment_method` enum('bank_transfer','mobile_money','paypal','other') DEFAULT NULL,
  `payment_details` varchar(255) DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `is_active`, `payment_method`, `payment_details`, `last_login_at`, `created_at`) VALUES
(1, 1, 'Nibishaka Radjab', 'nibishakaradjab5@gmail.com', '$2y$10$xwVKp4CdeHNIBUe2i4fx/uYLpYWMYbBgLTzyfZal/61Sin423HGEC', 1, NULL, NULL, '2026-10-30 11:41:13', '2026-10-27 09:01:50'),
(2, 4, 'receptionist', 'receptionist@gmail.com', '$2y$10$IIwnjRssyYoyZh5E45ZQq.NaWahCADcT38f7QDriEEF19sh8WeGCO', 1, NULL, NULL, '2026-10-30 10:32:46', '2026-10-27 09:03:32'),
(3, 3, 'nurse', 'nurse@gmail.com', '$2y$10$ihNXBsf2CgpNQYlCzF/7A.H.C7zNj6Ps/ZGkfd6HIlHSkiSufzBBe', 1, 'bank_transfer', '4011113393185', '2026-10-30 12:23:46', '2026-10-27 09:08:23'),
(4, 2, 'doctor', 'doctor@gmail.com', '$2y$10$3jcVyJxfTRSG.6inyMd8BuLszfmCOQCbJUAvoTrzgaDLBJKnE/haS', 1, NULL, NULL, '2026-10-30 18:48:54', '2026-10-27 09:12:29'),
(5, 12, 'human resource', 'humanresources@gmail.com', '$2y$10$2U2UiXivYK1RsQCfJf3d4u5I4yZ6jmtABpNCSpPFQEpvoL.bHtMTu', 1, NULL, NULL, '2026-10-30 18:56:27', '2026-10-30 07:09:48'),
(6, 13, 'driver', 'driver@gmai.com', '$2y$10$VZSZdsCdgrVAFLp0Whx2guHc6ohqnNhaFH4cNlA.C.oXZkkIAA89K', 1, NULL, NULL, NULL, '2026-10-30 08:41:38'),
(7, 5, 'pharmacist', 'pharmacist@gmail.com', '$2y$10$uNlXnyQkDU6ZYu2vbROsCuH/dNr0QHddaLNeWH2boXbpRrkTSOLcm', 1, NULL, NULL, '2026-10-30 10:57:24', '2026-10-30 08:56:11'),
(8, 14, 'uwimana damascene', 'damascene@gmail.com', '$2y$10$J7GYe1vTnDkc6J4p3NAvfOdUTv8gNnhfygjPF9fYoDjkxxwTirONO', 1, NULL, NULL, '2026-10-30 11:38:46', '2026-10-30 09:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `visits`
--

CREATE TABLE `visits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `patient_id` bigint(20) UNSIGNED NOT NULL,
  `doctor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `appointment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `visited_at` datetime NOT NULL DEFAULT current_timestamp(),
  `chief_complaint` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wards`
--

CREATE TABLE `wards` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `ward_type` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wards`
--

INSERT INTO `wards` (`id`, `name`, `ward_type`) VALUES
(1, 'General Ward', 'General');

-- --------------------------------------------------------

--
-- Table structure for table `work_logs`
--

CREATE TABLE `work_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `work_date` date NOT NULL,
  `category` varchar(40) NOT NULL,
  `summary` varchar(160) NOT NULL,
  `details` text NOT NULL,
  `duration_minutes` smallint(5) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_logs`
--

INSERT INTO `work_logs` (`id`, `user_id`, `work_date`, `category`, `summary`, `details`, `duration_minutes`, `created_at`) VALUES
(1, 4, '2026-10-27', 'radiology', 'xxcfvvcvx', 'cvfvdgdgfzg', 29, '2026-10-27 09:57:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admissions_active_patient` (`active_patient_id`),
  ADD UNIQUE KEY `uq_admissions_active_bed` (`active_bed_id`),
  ADD KEY `idx_admissions_status` (`status`,`admitted_at`),
  ADD KEY `fk_admissions_patient` (`patient_id`),
  ADD KEY `fk_admissions_bed` (`bed_id`),
  ADD KEY `fk_admissions_user` (`admitted_by`);

--
-- Indexes for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ambulance_status` (`status`,`requested_at`),
  ADD KEY `fk_ambulance_patient` (`patient_id`),
  ADD KEY `fk_ambulance_requester` (`requested_by`),
  ADD KEY `fk_ambulance_driver` (`driver_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_appointments_schedule` (`scheduled_at`,`status`),
  ADD KEY `fk_appointments_patient` (`patient_id`),
  ADD KEY `fk_appointments_doctor` (`doctor_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_logs_created` (`created_at`),
  ADD KEY `idx_audit_logs_actor` (`actor_user_id`,`created_at`);

--
-- Indexes for table `beds`
--
ALTER TABLE `beds`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_beds_ward_number` (`ward_id`,`bed_number`),
  ADD KEY `idx_beds_status` (`status`);

--
-- Indexes for table `conference_messages`
--
ALTER TABLE `conference_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_conf_msg_user` (`user_id`),
  ADD KEY `idx_conf_msg_room_time` (`room_id`,`sent_at`);

--
-- Indexes for table `conference_participants`
--
ALTER TABLE `conference_participants`
  ADD PRIMARY KEY (`room_id`,`user_id`),
  ADD KEY `fk_conf_part_user` (`user_id`);

--
-- Indexes for table `conference_rooms`
--
ALTER TABLE `conference_rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_code` (`room_code`),
  ADD KEY `fk_conf_room_creator` (`created_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `discharge_summaries`
--
ALTER TABLE `discharge_summaries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admission_id` (`admission_id`),
  ADD KEY `fk_discharge_summary_user` (`written_by`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `license_number` (`license_number`),
  ADD KEY `fk_doctors_department` (`department_id`);

--
-- Indexes for table `emergency_triage`
--
ALTER TABLE `emergency_triage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_triage_time` (`assessed_at`,`acuity`),
  ADD KEY `fk_triage_patient` (`patient_id`),
  ADD KEY `fk_triage_assessor` (`assessed_by`);

--
-- Indexes for table `equipment_assets`
--
ALTER TABLE `equipment_assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_tag` (`asset_tag`);

--
-- Indexes for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_maintenance_date` (`scheduled_on`,`status`),
  ADD KEY `fk_maintenance_asset` (`asset_id`),
  ADD KEY `fk_maintenance_user` (`recorded_by`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hospital_settings`
--
ALTER TABLE `hospital_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `fk_invoices_patient` (`patient_id`);

--
-- Indexes for table `lab_results`
--
ALTER TABLE `lab_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_test_id` (`lab_test_id`),
  ADD KEY `fk_lab_results_user` (`recorded_by`);

--
-- Indexes for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lab_tests_patient` (`patient_id`),
  ADD KEY `fk_lab_tests_user` (`requested_by`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_medicines_stock` (`quantity`,`reorder_level`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `nursing_notes`
--
ALTER TABLE `nursing_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nursing_patient_time` (`patient_id`,`recorded_at`),
  ADD KEY `fk_nursing_user` (`recorded_by`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_no` (`patient_no`),
  ADD KEY `idx_patients_name` (`last_name`,`first_name`),
  ADD KEY `idx_patients_phone` (`phone`),
  ADD KEY `fk_patients_creator` (`created_by`);

--
-- Indexes for table `patient_portal_accounts`
--
ALTER TABLE `patient_portal_accounts`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `patient_id` (`patient_id`);

--
-- Indexes for table `patient_services`
--
ALTER TABLE `patient_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient_services_patient_date` (`patient_id`,`service_date`,`created_at`),
  ADD KEY `idx_patient_services_staff_date` (`provided_by`,`service_date`,`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payments_invoice` (`invoice_id`),
  ADD KEY `fk_payments_user` (`received_by`);

--
-- Indexes for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_payroll_user_period` (`user_id`,`pay_period`),
  ADD KEY `fk_payroll_recorder` (`recorded_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_key` (`permission_key`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prescriptions_patient` (`patient_id`),
  ADD KEY `fk_prescriptions_doctor` (`doctor_id`),
  ADD KEY `fk_prescriptions_visit` (`visit_id`);

--
-- Indexes for table `prescription_dispensations`
--
ALTER TABLE `prescription_dispensations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prescription_item_id` (`prescription_item_id`),
  ADD KEY `fk_dispense_user` (`dispensed_by`);

--
-- Indexes for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_prescription_items_prescription` (`prescription_id`),
  ADD KEY `fk_prescription_items_medicine` (`medicine_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `idx_purchase_status` (`status`,`created_at`),
  ADD KEY `fk_purchase_supplier` (`supplier_id`),
  ADD KEY `fk_purchase_item` (`inventory_item_id`),
  ADD KEY `fk_purchase_user` (`created_by`);

--
-- Indexes for table `radiology_requests`
--
ALTER TABLE `radiology_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_radiology_status` (`status`,`requested_at`),
  ADD KEY `fk_radiology_patient` (`patient_id`),
  ADD KEY `fk_radiology_requester` (`requested_by`),
  ADD KEY `fk_radiology_performer` (`performed_by`);

--
-- Indexes for table `reports_log`
--
ALTER TABLE `reports_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_key` (`role_key`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_role_permissions_permission` (`permission_id`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_attendance_user_date` (`user_id`,`work_date`),
  ADD KEY `idx_attendance_date_status` (`work_date`,`status`),
  ADD KEY `fk_attendance_recorder` (`recorded_by`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_role` (`role_id`);

--
-- Indexes for table `visits`
--
ALTER TABLE `visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_visits_patient` (`patient_id`),
  ADD KEY `fk_visits_doctor` (`doctor_id`),
  ADD KEY `fk_visits_appointment` (`appointment_id`);

--
-- Indexes for table `wards`
--
ALTER TABLE `wards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `work_logs`
--
ALTER TABLE `work_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_work_logs_user_date` (`user_id`,`work_date`,`created_at`),
  ADD KEY `idx_work_logs_date_category` (`work_date`,`category`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `beds`
--
ALTER TABLE `beds`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `conference_messages`
--
ALTER TABLE `conference_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `conference_rooms`
--
ALTER TABLE `conference_rooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `discharge_summaries`
--
ALTER TABLE `discharge_summaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `emergency_triage`
--
ALTER TABLE `emergency_triage`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_assets`
--
ALTER TABLE `equipment_assets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lab_results`
--
ALTER TABLE `lab_results`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_tests`
--
ALTER TABLE `lab_tests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `nursing_notes`
--
ALTER TABLE `nursing_notes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patient_services`
--
ALTER TABLE `patient_services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescription_dispensations`
--
ALTER TABLE `prescription_dispensations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescription_items`
--
ALTER TABLE `prescription_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `radiology_requests`
--
ALTER TABLE `radiology_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports_log`
--
ALTER TABLE `reports_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `visits`
--
ALTER TABLE `visits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wards`
--
ALTER TABLE `wards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `work_logs`
--
ALTER TABLE `work_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `fk_admissions_bed` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`),
  ADD CONSTRAINT `fk_admissions_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `fk_admissions_user` FOREIGN KEY (`admitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ambulance_requests`
--
ALTER TABLE `ambulance_requests`
  ADD CONSTRAINT `fk_ambulance_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ambulance_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ambulance_requester` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `fk_appointments_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_appointments_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_logs_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `beds`
--
ALTER TABLE `beds`
  ADD CONSTRAINT `fk_beds_ward` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`id`);

--
-- Constraints for table `conference_messages`
--
ALTER TABLE `conference_messages`
  ADD CONSTRAINT `fk_conf_msg_room` FOREIGN KEY (`room_id`) REFERENCES `conference_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conf_msg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conference_participants`
--
ALTER TABLE `conference_participants`
  ADD CONSTRAINT `fk_conf_part_room` FOREIGN KEY (`room_id`) REFERENCES `conference_rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_conf_part_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conference_rooms`
--
ALTER TABLE `conference_rooms`
  ADD CONSTRAINT `fk_conf_room_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discharge_summaries`
--
ALTER TABLE `discharge_summaries`
  ADD CONSTRAINT `fk_discharge_summary_admission` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`),
  ADD CONSTRAINT `fk_discharge_summary_user` FOREIGN KEY (`written_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `fk_doctors_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `fk_doctors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `emergency_triage`
--
ALTER TABLE `emergency_triage`
  ADD CONSTRAINT `fk_triage_assessor` FOREIGN KEY (`assessed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_triage_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `equipment_maintenance`
--
ALTER TABLE `equipment_maintenance`
  ADD CONSTRAINT `fk_maintenance_asset` FOREIGN KEY (`asset_id`) REFERENCES `equipment_assets` (`id`),
  ADD CONSTRAINT `fk_maintenance_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `lab_results`
--
ALTER TABLE `lab_results`
  ADD CONSTRAINT `fk_lab_results_test` FOREIGN KEY (`lab_test_id`) REFERENCES `lab_tests` (`id`),
  ADD CONSTRAINT `fk_lab_results_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `lab_tests`
--
ALTER TABLE `lab_tests`
  ADD CONSTRAINT `fk_lab_tests_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `fk_lab_tests_user` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nursing_notes`
--
ALTER TABLE `nursing_notes`
  ADD CONSTRAINT `fk_nursing_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `fk_nursing_user` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `fk_patients_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `patient_portal_accounts`
--
ALTER TABLE `patient_portal_accounts`
  ADD CONSTRAINT `fk_patient_portal_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_patient_portal_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patient_services`
--
ALTER TABLE `patient_services`
  ADD CONSTRAINT `fk_patient_services_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `fk_patient_services_user` FOREIGN KEY (`provided_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `fk_payments_user` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  ADD CONSTRAINT `fk_payroll_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_payroll_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `fk_prescriptions_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prescriptions_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `fk_prescriptions_visit` FOREIGN KEY (`visit_id`) REFERENCES `visits` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `prescription_dispensations`
--
ALTER TABLE `prescription_dispensations`
  ADD CONSTRAINT `fk_dispense_item` FOREIGN KEY (`prescription_item_id`) REFERENCES `prescription_items` (`id`),
  ADD CONSTRAINT `fk_dispense_user` FOREIGN KEY (`dispensed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `prescription_items`
--
ALTER TABLE `prescription_items`
  ADD CONSTRAINT `fk_prescription_items_medicine` FOREIGN KEY (`medicine_id`) REFERENCES `medicines` (`id`),
  ADD CONSTRAINT `fk_prescription_items_prescription` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_purchase_item` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory` (`id`),
  ADD CONSTRAINT `fk_purchase_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `fk_purchase_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `radiology_requests`
--
ALTER TABLE `radiology_requests`
  ADD CONSTRAINT `fk_radiology_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  ADD CONSTRAINT `fk_radiology_performer` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_radiology_requester` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_attendance`
--
ALTER TABLE `staff_attendance`
  ADD CONSTRAINT `fk_attendance_recorder` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `visits`
--
ALTER TABLE `visits`
  ADD CONSTRAINT `fk_visits_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_visits_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_visits_patient` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`);

--
-- Constraints for table `work_logs`
--
ALTER TABLE `work_logs`
  ADD CONSTRAINT `fk_work_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
