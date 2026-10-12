-- Southbridge Rehabilitation User Role System
-- Run this in phpMyAdmin if you prefer SQL setup.
USE southbridge_loto;

ALTER TABLE users ADD COLUMN role VARCHAR(50) NOT NULL DEFAULT 'Work Order Inserty';
-- If the role column already exists, the ALTER above will report an error; that is safe to ignore.

-- Recommended roles:
-- Admin
-- Maintenance Supervisor
-- Maintenance
-- Pest Control
