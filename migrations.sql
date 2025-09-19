-- Add marked_records table for persistent marking storage
CREATE TABLE IF NOT EXISTS `marked_records`
(
    id          INT(10) NOT NULL AUTO_INCREMENT,
    zaznam_id   INT(10) NOT NULL,
    marked_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_id  VARCHAR(128),
    PRIMARY KEY (id),
    UNIQUE KEY unique_mark (zaznam_id, session_id),
    FOREIGN KEY (zaznam_id) REFERENCES zaznamy(id) ON DELETE CASCADE,
    INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- Add index for better performance on ordering
ALTER TABLE `zaznamy` ADD INDEX idx_jmeno (jmeno);
ALTER TABLE `zaznamy` ADD INDEX idx_prijmeni (prijmeni);