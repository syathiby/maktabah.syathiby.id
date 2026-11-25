<?php

namespace App\Libraries;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Label\Font\Font;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;

class QRGenerator
{
    protected QrCode $qrCode;
    protected PngWriter $writer;
    protected Logo $logo;
    protected Label $label;

    public function __construct(
        Color $foregroundColor = new Color(10, 15, 30),
        Color $backgroundColor = new Color(255, 255, 255),
        Color $textColor = new Color(10, 15, 30)
    ) {
        $this->writer = new PngWriter();

        $this->logo = Logo::create('')->setResizeToWidth(75);

        $this->label = Label::create('')->setTextColor($textColor);

        $this->qrCode = QrCode::create('')
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor($foregroundColor)
            ->setBackgroundColor($backgroundColor);
    }

    public function generateQRCode(
        string $data,
        string $labelText = null,
        string $dir = QR_CODES_PATH,
        string $filename = 'My QR Code',
        string $logoPath = null
    ) {
        if (!file_exists($dir)) mkdir($dir);

        // Nama file dibatasi ASCII karena label anggota/buku bisa memuat huruf
        // Arab, sedangkan nama file ini dipakai langsung sebagai URL.
        $baseName = stripNonAscii(truncateUtf8($filename, 16));

        $filename = url_title($baseName, lowercase: true) . '_'
            . substr(sha1($filename . rand(0, 1000)), 19, 5) . '_'
            . time() . '.png';

        // Save it to a file
        $this->writer
            ->write(
                qrCode: $this->qrCode->setData($data),
                label: $labelText ? $this->label->setText(toValidUtf8($labelText)) : null,
                logo: $logoPath ? $this->logo->setPath($logoPath) : null
            )
            ->saveToFile(path: $dir . $filename);

        return $filename;
    }
}
