USE ekyam_db;

-- Resource tags table
CREATE TABLE resource_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Resource-tag relationships table
CREATE TABLE resource_tag_relationships (
    resource_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (resource_id, tag_id),
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES resource_tags(id) ON DELETE CASCADE
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