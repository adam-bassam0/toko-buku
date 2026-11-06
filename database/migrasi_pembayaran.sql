-- ============================================
-- File    : database/migrasi_pembayaran.sql
-- Deskripsi: Migrasi untuk database yang sudah punya
--            tabel users, pesanan, dan detail_pesanan.
--            Menambahkan tabel pembayaran (QRIS & transfer).
-- Cara pakai: import lewat phpMyAdmin pada database db_toko_buku.
-- ============================================

USE db_toko_buku;

CREATE TABLE IF NOT EXISTS pembayaran (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    pesanan_id  INT(11)      NOT NULL,
    metode      ENUM('qris','transfer') NOT NULL,
    jumlah      DECIMAL(12,0) NOT NULL DEFAULT 0,
    status      ENUM('menunggu','lunas','gagal') NOT NULL DEFAULT 'menunggu',
    kode_bayar  VARCHAR(40)  NOT NULL,
    bukti       VARCHAR(255) DEFAULT NULL,
    dibayar_pada DATETIME    DEFAULT NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pembayaran_pesanan (pesanan_id),
    UNIQUE KEY uq_pembayaran_kode (kode_bayar),
    CONSTRAINT fk_pembayaran_pesanan FOREIGN KEY (pesanan_id)
        REFERENCES pesanan (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
