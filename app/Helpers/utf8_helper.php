<?php

/**
 * Bersihkan string dari byte yang bukan UTF-8 valid.
 *
 * preg_replace() dengan modifier /u mengembalikan null bila subjeknya bukan
 * UTF-8 valid. Nilai null itu kemudian jadi argumen $subject pada
 * pemanggilan preg_replace berikutnya dan memicu TypeError, misalnya di
 * dalam helper url_title().
 */
function toValidUtf8(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    if (mb_check_encoding($value, 'UTF-8')) {
        return $value;
    }

    return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
}

/**
 * Potong string per karakter, bukan per byte.
 *
 * substr() memotong per byte sehingga karakter multibyte (mis. huruf Arab)
 * bisa terpotong di tengah dan menghasilkan UTF-8 rusak.
 */
function truncateUtf8(?string $value, int $length): string
{
    $value = toValidUtf8($value);

    if ($length <= 0) {
        return '';
    }

    if (mb_strlen($value, 'UTF-8') <= $length) {
        return $value;
    }

    return mb_substr($value, 0, $length, 'UTF-8');
}

/**
 * Buang karakter non-ASCII dari string.
 *
 * Dipakai untuk nama file QR code: label anggota/buku bisa memuat huruf
 * Arab, sedangkan nama file tersebut dipakai langsung sebagai URL.
 */
function stripNonAscii(?string $value): string
{
    return preg_replace('/[^\x20-\x7E]/u', '', toValidUtf8($value));
}
