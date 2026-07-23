CREATE DATABASE IF NOT EXISTS taskflow_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taskflow_pro;

CREATE TABLE users(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(80) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','manager','member') NOT NULL DEFAULT 'member',
 avatar_color VARCHAR(20) NOT NULL DEFAULT '#3b82f6',
 bio VARCHAR(500) NULL,
 api_token VARCHAR(64) NOT NULL UNIQUE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tasks(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(150) NOT NULL,
 description TEXT NULL,
 status ENUM('todo','in_progress','completed') NOT NULL DEFAULT 'todo',
 priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
 due_date DATE NULL,
 category VARCHAR(80) NOT NULL DEFAULT 'General',
 progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_tasks_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_tasks_user_status(user_id,status),
 INDEX idx_tasks_user_priority(user_id,priority),
 INDEX idx_tasks_due(due_date)
) ENGINE=InnoDB;

CREATE TABLE notifications(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(120) NOT NULL,
 message VARCHAR(255) NOT NULL,
 is_read BOOLEAN NOT NULL DEFAULT FALSE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_notifications_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_notifications_user_read(user_id,is_read)
) ENGINE=InnoDB;

CREATE TABLE activity_logs(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 action VARCHAR(120) NOT NULL,
 details VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_activity_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_activity_user_created(user_id,created_at)
) ENGINE=InnoDB;

-- Demo password: Demo123!
INSERT INTO users(name,email,password_hash,role,avatar_color,bio,api_token) VALUES
('Demo Admin','demo@taskflow.test','$2y$12$4hs/5I8QH9yaxf3PyeapleP69oB69QenJLRMLQKguTJQ0xy.45gRu','admin','#3b82f6','Productivity workspace administrator','demo_api_token_123456789');
SET @uid=LAST_INSERT_ID();

INSERT INTO tasks(user_id,title,description,status,priority,due_date,category,progress) VALUES
(@uid,'Finalize portfolio case study','Write the problem, process and results.','in_progress','high',DATE_ADD(CURDATE(),INTERVAL 2 DAY),'Portfolio',65),
(@uid,'Prepare dashboard screenshots','Capture desktop and mobile layouts.','todo','medium',DATE_ADD(CURDATE(),INTERVAL 4 DAY),'Marketing',10),
(@uid,'Review database indexes','Confirm search and filter performance.','completed','medium',CURDATE(),'Development',100),
(@uid,'Deploy live demo','Publish to PHP hosting.','todo','high',DATE_ADD(CURDATE(),INTERVAL 5 DAY),'Deployment',0),
(@uid,'Write API documentation','Document authentication and endpoints.','in_progress','low',DATE_ADD(CURDATE(),INTERVAL 7 DAY),'Documentation',40);

INSERT INTO notifications(user_id,title,message) VALUES
(@uid,'Welcome to TaskFlow Pro','Your professional workspace is ready.'),
(@uid,'Portfolio milestone','Complete the live deployment to finish the case study.');

INSERT INTO activity_logs(user_id,action,details) VALUES
(@uid,'Account created','Demo administrator account'),
(@uid,'Task created','Finalize portfolio case study'),
(@uid,'Task completed','Review database indexes');
