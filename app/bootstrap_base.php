<?php
// ============================================
// File    : app/bootstrap_base.php
// Deskripsi: Bootstrap dasar tanpa koneksi database.
//            Memuat environment, konfigurasi aplikasi,
//            session, CSRF, dan helper auth.
// ============================================

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require_once __DIR__ . '/config/env.php';
env_load(APP_ROOT . '/.env');

require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/helpers/auth.php';
