<?php
// ============================================
// File    : config/auth.php
// Deskripsi: Wrapper kompatibilitas untuk kode lama.
//            Hanya memuat session/CSRF/auth (tanpa database).
//            Implementasi asli ada di app/helpers/auth.php.
// ============================================

require_once __DIR__ . '/../app/bootstrap_base.php';
