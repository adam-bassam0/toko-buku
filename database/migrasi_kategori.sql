-- ============================================
-- File    : database/migrasi_kategori.sql
-- Deskripsi: Migrasi untuk menambahkan fitur kategori buku
--            pada database yang sudah berjalan.
--            Membuat tabel kategori, menambahkan kolom
--            kategori_id pada tabel buku, dan memetakan
--            buku bawaan ke kategorinya.
-- Cara pakai: Import file ini lewat phpMyAdmin
--             pada database db_toko_buku.
-- ============================================

USE db_toko_buku_dev;

-- 1. Buat tabel kategori jika belum ada
CREATE TABLE IF NOT EXISTS kategori (
    id         INT(11)      NOT NULL AUTO_INCREMENT,
    nama       VARCHAR(100) NOT NULL,
    slug       VARCHAR(100) NOT NULL,
    deskripsi  TEXT         DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_kategori_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Tambah kolom kategori_id dan foreign key pada tabel buku secara aman
SET @dbname = DATABASE();
SET @tablename = "buku";
SET @columnname = "kategori_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE buku ADD COLUMN kategori_id INT(11) DEFAULT NULL AFTER penerbit, ADD CONSTRAINT fk_buku_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL ON UPDATE CASCADE;"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- 3. Isi data kategori awal
INSERT INTO kategori (id, nama, slug, deskripsi) VALUES
(1, 'Fiksi & Sastra', 'fiksi-sastra', 'Novel, cerpen, fiksi ilmiah, fantasi, dan karya sastra pilihan.'),
(2, 'Pengembangan Diri', 'pengembangan-diri', 'Buku motivasi, psikologi populer, finansial, dan pembentukan kebiasaan.'),
(3, 'Komik & Manga', 'komik-manga', 'Komik Jepang (manga), novel grafis, dan cerita bergambar populer.'),
(4, 'Pelajaran & Sains', 'pelajaran-sains', 'Buku teks akademik, sains, matematika, bahasa, dan ekonomi.'),
(5, 'Agama & Filsafat', 'agama-filsafat', 'Kajian spiritualitas, teks keagamaan, biografi tokoh, dan pandangan hidup.'),
(6, 'Anak-anak', 'anak-anak', 'Kisah klasik, dongeng, dan bacaan bergambar untuk anak dan remaja.')
ON DUPLICATE KEY UPDATE nama = VALUES(nama), deskripsi = VALUES(deskripsi);

-- 4. Petakan data buku yang ada ke kategori masing-masing
UPDATE buku SET kategori_id = 1 WHERE judul IN (
    'Laskar Pelangi', 'Bumi Manusia', 'Harry Potter dan Batu Bertuah',
    'It Ends with Us', 'The Seven Husbands of Evelyn Hugo', 'The Hunger Games',
    'Percy Jackson dan Pencuri Petir', 'Fourth Wing', 'Divergent',
    'The Fault in Our Stars', 'Twilight', 'The Midnight Library',
    'Where the Crawdads Sing', 'A Court of Thorns and Roses',
    'Throne of Glass', 'Red Queen', 'Normal People'
);

UPDATE buku SET kategori_id = 2 WHERE judul IN (
    'Atomic Habits', 'Filosofi Teras', 'The Psychology of Money',
    'The Subtle Art of Not Giving a F*ck', 'The Alchemist', 'Ikigai'
);

UPDATE buku SET kategori_id = 3 WHERE judul IN (
    'One Piece Vol. 1', 'Jujutsu Kaisen Vol. 1', 'Naruto Vol. 1',
    'Dragon Ball Vol. 1', 'Death Note Vol. 1', 'Attack on Titan Vol. 1',
    'Demon Slayer Vol. 1', 'My Hero Academia Vol. 1',
    'Fullmetal Alchemist Vol. 1', 'Tokyo Ghoul Vol. 1',
    'Maus: A Survivor\'s Tale I', 'Persepolis'
);

UPDATE buku SET kategori_id = 4 WHERE judul IN (
    'Campbell Biology', 'Chemistry: The Central Science',
    'Fundamentals of Physics', 'Concepts of Physics Vol. 1',
    'English Grammar in Use', 'Essential Grammar in Use',
    'Oxford Advanced Learner\'s Dictionary', 'The Elements',
    'Calculus', 'Principles of Economics'
);

UPDATE buku SET kategori_id = 5 WHERE judul IN (
    'The Study Quran', 'Muhammad: His Life Based on the Earliest Sources',
    'The Bhagavad Gita', 'The Heart of the Buddha\'s Teaching',
    'Mere Christianity'
);

UPDATE buku SET kategori_id = 6 WHERE judul IN (
    'The Little Prince', 'Charlotte\'s Web',
    'The Lion, the Witch and the Wardrobe', 'Matilda',
    'Charlie and the Chocolate Factory', 'Diary of a Wimpy Kid', 'Wonder'
);
