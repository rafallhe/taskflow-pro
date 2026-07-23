USE taskflow_pro;

ALTER TABLE users
ADD COLUMN IF NOT EXISTS last_login_at DATETIME NULL AFTER api_token;

CREATE TABLE IF NOT EXISTS login_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 user_id BIGINT UNSIGNED NOT NULL,
 ip_address VARCHAR(64) NULL,
 user_agent VARCHAR(255) NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_login_logs_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 INDEX idx_login_logs_user_created(user_id, created_at)
) ENGINE=InnoDB;
