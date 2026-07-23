USE taskflow_pro;

CREATE TABLE IF NOT EXISTS teams (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 owner_id BIGINT UNSIGNED NOT NULL,
 name VARCHAR(120) NOT NULL,
 slug VARCHAR(140) NOT NULL UNIQUE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_teams_owner FOREIGN KEY(owner_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_teams_owner(owner_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS team_members (
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

CREATE TABLE IF NOT EXISTS team_invitations (
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

ALTER TABLE tasks
ADD COLUMN IF NOT EXISTS team_id BIGINT UNSIGNED NULL AFTER user_id,
ADD COLUMN IF NOT EXISTS assigned_to BIGINT UNSIGNED NULL AFTER team_id;

ALTER TABLE tasks
ADD CONSTRAINT fk_tasks_team FOREIGN KEY(team_id) REFERENCES teams(id) ON DELETE SET NULL,
ADD CONSTRAINT fk_tasks_assignee FOREIGN KEY(assigned_to) REFERENCES users(id) ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_tasks_team ON tasks(team_id);
CREATE INDEX IF NOT EXISTS idx_tasks_assignee ON tasks(assigned_to);

INSERT INTO teams(owner_id,name,slug)
SELECT u.id,'Demo Team','demo-team'
FROM users u
WHERE u.email='demo@taskflow.test'
AND NOT EXISTS(SELECT 1 FROM teams WHERE slug='demo-team');

INSERT IGNORE INTO team_members(team_id,user_id,role)
SELECT t.id,u.id,'admin'
FROM teams t JOIN users u ON u.email='demo@taskflow.test'
WHERE t.slug='demo-team';

UPDATE tasks t
JOIN users u ON u.id=t.user_id AND u.email='demo@taskflow.test'
JOIN teams tm ON tm.slug='demo-team'
SET t.team_id=tm.id, t.assigned_to=u.id
WHERE t.team_id IS NULL;
