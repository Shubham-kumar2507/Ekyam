USE ekyam_db;

-- Add notification preference columns to users table
ALTER TABLE users
ADD COLUMN email_notifications BOOLEAN DEFAULT TRUE,
ADD COLUMN project_updates BOOLEAN DEFAULT TRUE,
ADD COLUMN community_updates BOOLEAN DEFAULT TRUE; 