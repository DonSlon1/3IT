CREATE TABLE IF NOT EXISTS `zaznamy`
(
    id       INT(10) NOT NULL AUTO_INCREMENT,
    jmeno    VARCHAR(64) NOT NULL,
    prijmeni VARCHAR(64) NOT NULL,
    datum    DATE,
    PRIMARY KEY (id)
);