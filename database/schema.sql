-- ============================================
-- File    : database/schema.sql
-- Proyek  : Toko Buku Online
-- Deskripsi: Script SQL untuk membuat database,
--            tabel, dan data contoh awal.
-- ============================================

-- 1. Buat dan gunakan database
CREATE DATABASE IF NOT EXISTS db_toko_buku
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_toko_buku;

-- 2. Buat tabel buku
--    Kolom cover menyimpan URL gambar dari internet (string)
CREATE TABLE IF NOT EXISTS buku (
    id       INT(11)        NOT NULL AUTO_INCREMENT,
    judul    VARCHAR(150)   NOT NULL,
    penulis  VARCHAR(100)   NOT NULL,
    penerbit VARCHAR(100)   NOT NULL,
    harga    DECIMAL(12,0)  NOT NULL DEFAULT 0,
    stok     INT(11)        NOT NULL DEFAULT 0,
    deskripsi TEXT,
    cover    VARCHAR(255)   DEFAULT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Data contoh (INSERT INTO)
INSERT INTO buku (judul, penulis, penerbit, harga, stok, deskripsi, cover) VALUES
(
    'Laskar Pelangi',
    'Andrea Hirata',
    'Bentang Pustaka',
    89000,
    25,
    'Novel pertama dari Tetralogi Laskar Pelangi karya Andrea Hirata. Berkisah tentang sepuluh anak Belitung yang berjuang mendapatkan pendidikan di bawah guru-guru yang luar biasa. Sebuah kisah yang menginspirasi jutaan pembaca di seluruh dunia.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1348613305i/1331181.jpg'
),
(
    'Bumi Manusia',
    'Pramoedya Ananta Toer',
    'Lentera Dipantara',
    95000,
    18,
    'Novel pertama dari Tetralogi Buru karya Pramoedya Ananta Toer. Berlatar belakang awal abad ke-20 di masa penjajahan Belanda, mengisahkan perjalanan hidup Minke, seorang pemuda pribumi yang cerdas dan penuh semangat.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1388186521i/282748.jpg'
),
(
    'Atomic Habits',
    'James Clear',
    'Gramedia Pustaka Utama',
    119000,
    40,
    'Buku panduan revolusioner untuk membangun kebiasaan baik dan menghilangkan kebiasaan buruk. James Clear mengungkap cara kerja kebiasaan dan memberikan strategi praktis berdasarkan penelitian ilmiah untuk membuat perubahan kecil yang menghasilkan hasil luar biasa.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1655988385i/40121378.jpg'
),
(
    'Filosofi Teras',
    'Henry Manampiring',
    'Kompas',
    98000,
    30,
    'Buku yang memperkenalkan filsafat Stoisisme kuno dengan cara yang relevan dan mudah dipahami oleh pembaca modern Indonesia. Berisi panduan praktis untuk menghadapi emosi negatif, kecemasan, dan ketidakpastian hidup sehari-hari.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1560248449i/45894108.jpg'
),
(
    'Harry Potter dan Batu Bertuah',
    'J.K. Rowling',
    'Gramedia Pustaka Utama',
    129000,
    15,
    'Kisah petualangan seorang anak yatim piatu bernama Harry Potter yang pada hari ulang tahunnya yang ke-11 mengetahui bahwa dirinya adalah seorang penyihir. Ia kemudian belajar di Hogwarts School of Witchcraft and Wizardry.',
    'https://images-na.ssl-images-amazon.com/images/S/compressed.photo.goodreads.com/books/1474154022i/3.jpg'
);

