<?php
// ============================================
// File    : app/config/env.php
// Deskripsi: Pembaca file .env sederhana tanpa dependency eksternal.
//            Nilai environment yang sudah ada tidak ditimpa.
// ============================================

/**
 * Muat variabel dari file .env ke environment.
 * Format: KEY=value
 * Baris komentar diawali tanda #.
 */
function env_load(string $filePath): void
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Dukung format "export KEY=value".
        if (str_starts_with($line, 'export ')) {
            $line = trim(substr($line, 7));
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key   = trim($parts[0]);
        $value = trim($parts[1]);

        // Buang tanda kutip pembungkus jika ada.
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last  = $value[strlen($value) - 1];
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $value = substr($value, 1, -1);
            }
        }

        if ($key === '' || getenv($key) !== false) {
            continue;
        }

        putenv($key . '=' . $value);
        $_ENV[$key] = $value;
    }
}

/**
 * Ambil nilai environment dengan nilai cadangan (fallback).
 */
function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}
