-- Staff conference rooms (video via Jitsi, text chat via DB polling).
-- Apply after migration 015.
USE hospital_management;

CREATE TABLE IF NOT EXISTS conference_rooms (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(160) NOT NULL,
    type        ENUM('chat','video') NOT NULL DEFAULT 'chat',
    room_code   VARCHAR(80)  NOT NULL UNIQUE,
    created_by  BIGINT UNSIGNED NOT NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_conf_room_creator FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS conference_participants (
    room_id     BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (room_id, user_id),
    CONSTRAINT fk_conf_part_room FOREIGN KEY (room_id) REFERENCES conference_rooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_conf_part_user FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS conference_messages (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room_id     BIGINT UNSIGNED NOT NULL,
    user_id     BIGINT UNSIGNED NOT NULL,
    message     TEXT NOT NULL,
    sent_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_conf_msg_room FOREIGN KEY (room_id) REFERENCES conference_rooms(id) ON DELETE CASCADE,
    CONSTRAINT fk_conf_msg_user FOREIGN KEY (user_id)  REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conf_msg_room_time (room_id, sent_at)
) ENGINE=InnoDB;

INSERT IGNORE INTO schema_migrations (version) VALUES ('2026_10_27_016_conference');
