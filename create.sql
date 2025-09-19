CREATE TABLE IF NOT EXISTS `zaznamy`
(
    id       INT(10) NOT NULL AUTO_INCREMENT,
    jmeno    VARCHAR(64) NOT NULL,
    prijmeni VARCHAR(64) NOT NULL,
    datum    DATE,
    PRIMARY KEY (id),
    UNIQUE KEY unique_person (jmeno, prijmeni),
    INDEX idx_datum (datum)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;