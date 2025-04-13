-- Create the database
CREATE DATABASE IF NOT EXISTS ekyam_db;
USE ekyam_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    user_type ENUM('individual', 'community_admin', 'system_admin') NOT NULL,
    location VARCHAR(100),
    bio TEXT,                             -- Added bio for user profiles
    profile_image VARCHAR(255),           -- Added profile image path
    community_id INT,
    email_notifications BOOLEAN DEFAULT TRUE,  -- Added email notification preference
    project_updates BOOLEAN DEFAULT TRUE,      -- Added project updates preference
    community_updates BOOLEAN DEFAULT TRUE,    -- Added community updates preference
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Communities table
CREATE TABLE communities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,            -- Made description required
    location VARCHAR(100) NOT NULL,       -- Made location required
    image VARCHAR(255),                   -- Added community image
    category VARCHAR(50),                 -- Added category for filtering communities
    member_count INT DEFAULT 0,           -- Added counter for total members
    admin_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add foreign key constraint to users table after communities table is created
ALTER TABLE users
ADD CONSTRAINT fk_user_community
FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE SET NULL;

-- Projects table
CREATE TABLE projects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,            -- Made description required
    status ENUM('planning', 'in_progress', 'completed', 'on_hold') NOT NULL DEFAULT 'planning',
    image VARCHAR(255),                   -- Added project image
    start_date DATE NOT NULL,             -- Made start_date required
    end_date DATE,
    community_id INT,
    is_featured BOOLEAN DEFAULT FALSE,    -- Added flag for featured projects on homepage
    created_by INT NOT NULL,
    member_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Project members table (for users participating in projects)
CREATE TABLE project_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50) NOT NULL,            -- Made role required
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (project_id, user_id)
);

-- Resources table
CREATE TABLE resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,            -- Made description required
    type ENUM('document', 'link', 'video', 'image', 'other') NOT NULL,
    url VARCHAR(255),
    file_path VARCHAR(255),
    community_id INT,
    project_id INT,                       -- Added direct link to projects
    is_public BOOLEAN DEFAULT FALSE,      -- Added flag for public resources
    download_count INT DEFAULT 0,         -- Track resource popularity
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Resource access table (for tracking which users have access to resources)
CREATE TABLE resource_access (
    id INT PRIMARY KEY AUTO_INCREMENT,
    resource_id INT NOT NULL,
    user_id INT NOT NULL,
    access_level ENUM('read', 'edit', 'admin') NOT NULL DEFAULT 'read',  -- Made access_level required
    granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (resource_id, user_id)
);

-- Project resources table (for resources associated with specific projects)
CREATE TABLE project_resources (
    id INT PRIMARY KEY AUTO_INCREMENT,
    project_id INT NOT NULL,
    resource_id INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    UNIQUE (project_id, resource_id)
);

-- Community members table (for users that are part of communities but not admins)
CREATE TABLE community_members (
    id INT PRIMARY KEY AUTO_INCREMENT,
    community_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'member',  -- Made role required
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (community_id, user_id)
);

-- Community map coordinates table
CREATE TABLE community_locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    community_id INT NOT NULL UNIQUE,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    address VARCHAR(255),                 -- Added address field for geocoding
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE
);

-- Messages/communications table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT,
    community_id INT,
    project_id INT,
    subject VARCHAR(100) NOT NULL,        -- Made subject required
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Newsletter subscribers table (for the newsletter signup in footer)
CREATE TABLE newsletter_subscribers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Testimonials table (for community stories section)
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    community_id INT,
    content TEXT NOT NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE
);

-- Password reset tokens table
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Resource tags table
CREATE TABLE resource_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Resource-tag relationships
CREATE TABLE resource_tag_relationships (
    resource_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (resource_id, tag_id),
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES resource_tags(id) ON DELETE CASCADE
);

-- Resource-community relationships (for multiple communities)
CREATE TABLE resource_community_relationships (
    resource_id INT NOT NULL,
    community_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (resource_id, community_id),
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE
);

-- Resource-project relationships (for multiple projects)
CREATE TABLE resource_project_relationships (
    resource_id INT NOT NULL,
    project_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (resource_id, project_id),
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Add some default resource tags
INSERT INTO resource_tags (name) VALUES 
('Education'),
('Technology'),
('Environment'),
('Health'),
('Agriculture'),
('Business'),
('Art'),
('Science'),
('Culture'),
('Development');

-- Sample data: Create first system admin
INSERT INTO users (username, email, password, full_name, user_type)
VALUES ('admin', 'admin@ekyam.org', '$2y$10$YourHashedPasswordHere', 'System Administrator', 'system_admin');

-- Triggers to update member counts
DELIMITER //

-- Update project member count when members are added or removed
CREATE TRIGGER update_project_members_count_insert AFTER INSERT ON project_members
FOR EACH ROW
BEGIN
    UPDATE projects SET member_count = (
        SELECT COUNT(*) FROM project_members WHERE project_id = NEW.project_id
    ) WHERE id = NEW.project_id;
END//

CREATE TRIGGER update_project_members_count_delete AFTER DELETE ON project_members
FOR EACH ROW
BEGIN
    UPDATE projects SET member_count = (
        SELECT COUNT(*) FROM project_members WHERE project_id = OLD.project_id
    ) WHERE id = OLD.project_id;
END//

-- Update community member count when members are added or removed
CREATE TRIGGER update_community_members_count_insert AFTER INSERT ON community_members
FOR EACH ROW
BEGIN
    UPDATE communities SET member_count = (
        SELECT COUNT(*) FROM community_members WHERE community_id = NEW.community_id
    ) + 1 WHERE id = NEW.community_id;  -- +1 for admin
END//

CREATE TRIGGER update_community_members_count_delete AFTER DELETE ON community_members
FOR EACH ROW
BEGIN
    UPDATE communities SET member_count = (
        SELECT COUNT(*) FROM community_members WHERE community_id = OLD.community_id
    ) + 1 WHERE id = OLD.community_id;  -- +1 for admin
END//

DELIMITER ;