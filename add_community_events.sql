USE ekyam_db;

-- Community events table
CREATE TABLE community_events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    community_id INT NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATETIME NOT NULL,
    end_date DATETIME,
    location VARCHAR(255),
    event_type ENUM('meeting', 'workshop', 'social', 'other') NOT NULL DEFAULT 'other',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Event participants table
CREATE TABLE event_participants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('attending', 'maybe', 'declined') NOT NULL DEFAULT 'attending',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES community_events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE (event_id, user_id)
); 