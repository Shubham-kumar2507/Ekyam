USE ekyam_db;

-- Add community settings columns to communities table
ALTER TABLE communities
ADD COLUMN is_public BOOLEAN DEFAULT TRUE,
ADD COLUMN allow_member_invites BOOLEAN DEFAULT TRUE,
ADD COLUMN require_approval BOOLEAN DEFAULT FALSE; 