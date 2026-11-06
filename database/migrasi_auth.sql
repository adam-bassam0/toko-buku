-- ============================================
-- File    : database/migrasi_auth.sql
-- Deskripsi: Migrasi untuk database yang SUDAH ADA
--            (tidak perlu import ulang data buku).
--            Menambahkan tabel users, pesanan,
--            detail_pesanan, dan akun contoh.
-- Cara pakai: import file ini lewat phpMyAdmin
--             pada database db_toko_buku.
-- ============================================

USE db_toko_buku;

CREATE TABLE IF NOT EXISTS users (
    id         INT(11)      NOT NULL AUTO_INCREMENT,
    nama       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('user','admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pesanan (
    id         INT(11)      NOT NULL AUTO_INCREMENT,
    user_id    INT(11)      NOT NULL,
    total      DECIMAL(12,0) NOT NULL DEFAULT 0,
    status     ENUM('pending','dibayar','dikirim','selesai','batal')
               NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pesanan_user (user_id),
    CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS detail_pesanan (
    id         INT(11)       NOT NULL AUTO_INCREMENT,
    pesanan_id INT(11)       NOT NULL,
    buku_id    INT(11)       DEFAULT NULL,
    judul      VARCHAR(150)  NOT NULL,
    harga      DECIMAL(12,0) NOT NULL,
    jumlah     INT(11)       NOT NULL,
    subtotal   DECIMAL(12,0) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_detail_pesanan (pesanan_id),
    CONSTRAINT fk_detail_pesanan FOREIGN KEY (pesanan_id)
        REFERENCES pesanan (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_detail_buku FOREIGN KEY (buku_id)
        REFERENCES buku (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun contoh (password sudah di-hash bcrypt)
INSERT IGNORE INTO users (nama, email, password, role) VALUES
('Admin BukuKu', 'admin@bukuku.test', '$2y$10$AdwFFHLktZ11s/kyNKZi0.rLuCboqmhxrrFknVG60iz3qFMdbMMPW', 'admin'),
('Budi Santoso', 'budi@bukuku.test', '$2y$10$/Vb9/3NJ8RWI2SZ2dQPSiOuCr2xVI2Ehl7gBO2EJDqmU88FTQVX4W', 'user');
