CREATE TABLE kavsaklar (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kavsak_adi VARCHAR(100) NOT NULL
);

CREATE TABLE trafik_lambalari (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kavsak_id BIGINT UNSIGNED NOT NULL,
    yon VARCHAR(10) NOT NULL,
    suanki_renk VARCHAR(10) DEFAULT 'KIRMIZI',
    kalan_sure INT UNSIGNED DEFAULT 0,
    kirmizi_sure INT UNSIGNED DEFAULT 60,
    yesil_sure INT UNSIGNED DEFAULT 20,
    sari_sure INT UNSIGNED DEFAULT 3,
    FOREIGN KEY (kavsak_id) REFERENCES kavsaklar (id)
);

INSERT INTO kavsaklar (kavsak_adi) VALUES ('Alanya Meydanı');

INSERT INTO
    trafik_lambalari (
        kavsak_id,
        yon,
        suanki_renk,
        kalan_sure
    )
VALUES (1, 'KUZEY', 'YESIL', 20),
    (1, 'GUNEY', 'YESIL', 20),
    (1, 'DOGU', 'KIRMIZI', 23),
    (1, 'BATI', 'KIRMIZI', 23);

UPDATE trafik_lambalari
SET
    suanki_renk = 'KIRMIZI'
WHERE
    yon = 'KUZEY';

/* kavsaklar ile trafiklambaları kavsak_id yi birlestirdik  */

SELECT kavsaklar.kavsak_adi, trafik_lambalari.yon, trafik_lambalari.suanki_renk, trafik_lambalari.kalan_sure
FROM trafik_lambalari
    INNER JOIN kavsaklar ON trafik_lambalari.kavsak_id = kavsaklar.id;