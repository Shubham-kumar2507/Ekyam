USE ekyam_db;

-- Community activity table
CREATE TABLE community_activity (
    id INT PRIMARY KEY AUTO_INCREMENT,
    community_id INT NOT NULL,
    user_id INT NOT NULL,
    activity_type ENUM('join', 'leave', 'create_project', 'update_project', 'upload_resource', 'download_resource', 'post_message', 'update_role') NOT NULL,
    activity_details TEXT,
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (community_id) REFERENCES communities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Trigger to log member joins
DELIMITER //
CREATE TRIGGER log_member_join AFTER INSERT ON community_members
FOR EACH ROW
BEGIN
    INSERT INTO community_activity (community_id, user_id, activity_type, activity_details)
    VALUES (NEW.community_id, NEW.user_id, 'join', CONCAT('Joined as ', NEW.role));
END //
DELIMITER ;

-- Trigger to log member leaves
DELIMITER //
CREATE TRIGGER log_member_leave AFTER DELETE ON community_members
FOR EACH ROW
BEGIN
    INSERT INTO community_activity (community_id, user_id, activity_type, activity_details)
    VALUES (OLD.community_id, OLD.user_id, 'leave', 'Left the community');
END //
DELIMITER ;

-- Trigger to log role updates
DELIMITER //
CREATE TRIGGER log_role_update AFTER UPDATE ON community_members
FOR EACH ROW
BEGIN
    IF NEW.role != OLD.role THEN
        INSERT INTO community_activity (community_id, user_id, activity_type, activity_details)
        VALUES (NEW.community_id, NEW.user_id, 'update_role', CONCAT('Role changed from ', OLD.role, ' to ', NEW.role));
    END IF;
END //
DELIMITER ; 