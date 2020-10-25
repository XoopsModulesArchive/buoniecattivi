CREATE TABLE buoniecattivi_voti (
    idvoto INT(11)     NOT NULL AUTO_INCREMENT,
    uid    INT(5)      NOT NULL,
    come   VARCHAR(20) NOT NULL,
    ipvoto VARCHAR(15) NOT NULL,
    motivo TEXT,
    data   DATE,
    PRIMARY KEY (idvoto),
    KEY (uid)
)
    ENGINE = ISAM;
