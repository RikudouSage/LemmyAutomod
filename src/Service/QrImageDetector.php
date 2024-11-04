<?php

namespace App\Service;

use chillerlan\QRCode\Detector\QRCodeDetectorException;
use chillerlan\QRCode\QRCode;
use Zxing\QrReader;

final readonly class QrImageDetector
{
    public function getQrCodeContent(string $path): ?string
    {
        try {
            $qr = (new QRCode())->readFromFile($path);
            return $qr->data;
        } catch (QRCodeDetectorException) {
            return null;
        }
    }
}
