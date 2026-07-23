CREATE DATABASE IF NOT EXISTS taskflow_pro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taskflow_pro;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS email_queue;
DROP TABLE IF EXISTS login_logs;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS task_attachments;
DROP TABLE IF EXISTS task_checklists;
DROP TABLE IF EXISTS task_comments;
DROP TABLE IF EXISTS team_invitations;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS team_members;
DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(80) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','manager','member') NOT NULL DEFAULT 'member',
 avatar_color VARCHAR(20) NOT NULL DEFAULT '#3b82f6',
 bio VARCHAR(500) NULL,
 api_token VARCHAR(64) NOT NULL UNIQUE,
 last_login_at DATETIME NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE teams(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 owner_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(120) NOT NULL,
 description VARCHAR(500) NULL,
 color VARCHAR(20) NOT NULL DEFAULT '#3b82f6',
 logo_initials VARCHAR(4) NULL,
 slug VARCHAR(140) NOT NULL UNIQUE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_teams_owner FOREIGN KEY(owner_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_teams_owner(owner_id)
) ENGINE=InnoDB;

CREATE TABLE team_members(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 team_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL,
 role ENUM('admin','manager','member') NOT NULL DEFAULT 'member',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_team_members_team FOREIGN KEY(team_id) REFERENCES teams(id) ON DELETE CASCADE,
 CONSTRAINT fk_team_members_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 UNIQUE KEY uq_team_member(team_id,user_id),
 INDEX idx_team_members_user(user_id)
) ENGINE=InnoDB;

CREATE TABLE team_invitations(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 team_id BIGINT UNSIGNED NOT NULL,
 email VARCHAR(190) NOT NULL,
 token VARCHAR(64) NOT NULL UNIQUE,
 role ENUM('manager','member') NOT NULL DEFAULT 'member',
 status ENUM('pending','accepted','expired') NOT NULL DEFAULT 'pending',
 expires_at DATETIME NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_team_invites_team FOREIGN KEY(team_id) REFERENCES teams(id) ON DELETE CASCADE,
 INDEX idx_team_invites_email(email),
 INDEX idx_team_invites_status(status)
) ENGINE=InnoDB;

CREATE TABLE tasks(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 team_id BIGINT UNSIGNED NULL,
 assigned_to BIGINT UNSIGNED NULL,
 title VARCHAR(150) NOT NULL,
 description TEXT NULL,
 status ENUM('todo','in_progress','completed') NOT NULL DEFAULT 'todo',
 priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
 due_date DATE NULL,
 category VARCHAR(80) NOT NULL DEFAULT 'General',
 tags VARCHAR(255) NULL,
 progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_tasks_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_tasks_team FOREIGN KEY(team_id) REFERENCES teams(id) ON DELETE SET NULL,
 CONSTRAINT fk_tasks_assignee FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_tasks_user_status(user_id,status),
 INDEX idx_tasks_user_priority(user_id,priority),
 INDEX idx_tasks_due(due_date),
 INDEX idx_tasks_team(team_id),
 INDEX idx_tasks_assignee(assigned_to)
) ENGINE=InnoDB;

CREATE TABLE task_comments(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL,
 body TEXT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_comments_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 CONSTRAINT fk_comments_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_comments_task_created(task_id,created_at)
) ENGINE=InnoDB;

CREATE TABLE task_checklists(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id BIGINT UNSIGNED NOT NULL,
 item_text VARCHAR(255) NOT NULL,
 is_done BOOLEAN NOT NULL DEFAULT FALSE,
 position_no INT NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_checklists_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 INDEX idx_checklists_task(task_id,position_no)
) ENGINE=InnoDB;

CREATE TABLE task_attachments(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 task_id BIGINT UNSIGNED NOT NULL,
 user_id BIGINT UNSIGNED NOT NULL,
 original_name VARCHAR(255) NOT NULL,
 stored_name VARCHAR(255) NOT NULL UNIQUE,
 mime_type VARCHAR(120) NOT NULL,
 file_size BIGINT UNSIGNED NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_attachments_task FOREIGN KEY(task_id) REFERENCES tasks(id) ON DELETE CASCADE,
 CONSTRAINT fk_attachments_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_attachments_task(task_id,created_at)
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

CREATE TABLE login_logs(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 ip_address VARCHAR(64) NULL,
 user_agent VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_login_logs_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_login_logs_user_created(user_id,created_at)
) ENGINE=InnoDB;

CREATE TABLE email_queue(
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 recipient VARCHAR(190) NOT NULL,
 subject VARCHAR(190) NOT NULL,
 body TEXT NOT NULL,
 status ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
 attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
 last_error VARCHAR(500) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 sent_at DATETIME NULL,
 INDEX idx_email_queue_status(status,created_at)
) ENGINE=InnoDB;

-- Demo password: Demo123!
INSERT INTO users(name,email,password_hash,role,avatar_color,bio,api_token)
VALUES('Demo Admin','demo@taskflow.test','$2y$10$ac9N0f0Y6D2qW7uquO49FuHPSWSzDqJ7oeTsk4TFMb5SGiO9N0QfK','admin','#3b82f6','Productivity workspace administrator','demo_api_token_123456789');
SET @uid=LAST_INSERT_ID();

INSERT INTO teams(owner_id,name,slug) VALUES(@uid,'Demo Team','demo-team');
SET @team=LAST_INSERT_ID();

INSERT INTO team_members(team_id,user_id,role) VALUES(@team,@uid,'admin');

INSERT INTO tasks(user_id,team_id,assigned_to,title,description,status,priority,due_date,category,tags,progress) VALUES
(@uid,@team,@uid,'Finalize portfolio case study','Write the problem, process and results.','in_progress','high',DATE_ADD(CURDATE(),INTERVAL 2 DAY),'Portfolio','portfolio,documentation',65),
(@uid,@team,@uid,'Prepare dashboard screenshots','Capture desktop and mobile layouts.','todo','medium',DATE_ADD(CURDATE(),INTERVAL 4 DAY),'Marketing','screenshots,ui',10),
(@uid,@team,@uid,'Review database indexes','Confirm search and filter performance.','completed','medium',CURDATE(),'Development','database,performance',100),
(@uid,@team,@uid,'Deploy live demo','Publish to PHP hosting.','todo','high',DATE_ADD(CURDATE(),INTERVAL 5 DAY),'Deployment','hosting,production',0),
(@uid,@team,@uid,'Write API documentation','Document authentication and endpoints.','in_progress','low',DATE_ADD(CURDATE(),INTERVAL 7 DAY),'Documentation','api,docs',40);

INSERT INTO notifications(user_id,title,message) VALUES
(@uid,'Welcome to TaskFlow Pro','Your professional workspace is ready.'),
(@uid,'Portfolio milestone','Complete the live deployment to finish the case study.');

INSERT INTO activity_logs(user_id,action,details) VALUES
(@uid,'Account created','Demo administrator account'),
(@uid,'Task created','Finalize portfolio case study'),
(@uid,'Task completed','Review database indexes');
