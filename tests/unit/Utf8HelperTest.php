<?php

use App\Libraries\QRGenerator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class Utf8HelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // helper 'utf8' didaftarkan di app/Config/Autoload.php
        $this->assertTrue(function_exists('truncateUtf8'), 'Helper utf8 harus ter-autoload');
    }

    public function testToValidUtf8KeepsValidString()
    {
        $this->assertSame('مستدرك', toValidUtf8('مستدرك'));
    }

    public function testToValidUtf8RepairsBrokenBytes()
    {
        // Huruf Arab yang terpotong di tengah byte
        $broken = "Ustadz Khoer_\xd9\x85\xd8";

        $cleaned = toValidUtf8($broken);

        $this->assertTrue(mb_check_encoding($cleaned, 'UTF-8'));
    }

    public function testTruncateUtf8DoesNotSplitMultibyteCharacter()
    {
        $truncated = truncateUtf8('مستدرك عدن أصل الشرح', 6);

        $this->assertTrue(mb_check_encoding($truncated, 'UTF-8'));
        $this->assertSame('مستدرك', $truncated);
    }

    public function testTruncateUtf8KeepsShortStringIntact()
    {
        $this->assertSame('Ustadz Khoer', truncateUtf8('Ustadz Khoerudin', 12));
    }

    public function testStripNonAsciiRemovesNonAsciiCharacters()
    {
        $this->assertSame('Ustadz Khoer_', stripNonAscii('Ustadz Khoer_مستدرك'));
    }

    /**
     * Reproduksi error produksi: label peminjaman berisi huruf Arab dipotong
     * per byte, hasil potongannya bukan UTF-8 valid sehingga url_title()
     * membuat preg_replace() mengembalikan null dan memicu TypeError.
     */
    public function testGenerateQRCodeWithArabicBookTitle()
    {
        $memberName = 'Ustadz Khoerudin';
        $bookTitle = 'مستدرك عدن أصل الشرح (Al Mustadrak Hakim) (2007)';

        $label = truncateUtf8($memberName, 12) . '_' . truncateUtf8($bookTitle, 12);

        $dir = sys_get_temp_dir() . '/syathiby_qr_test/';

        $qrGenerator = new QRGenerator();

        $filename = $qrGenerator->generateQRCode(
            data: 'test-uid',
            labelText: $label,
            dir: $dir,
            filename: $label
        );

        $this->assertFileExists($dir . $filename);
        $this->assertSame(1, preg_match('/^[\x20-\x7E]+$/', $filename), 'Nama file QR code harus ASCII');

        unlink($dir . $filename);
    }
}
