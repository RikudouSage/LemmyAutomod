<?php

namespace App\Service;

final readonly class ImageManipulator
{
    public function invertColors(string $fileContent): string
    {
        $img = imagecreatefromstring($fileContent);
        imagefilter($img, IMG_FILTER_NEGATE);

        $stream = fopen('php://memory','r+');
        imagepng($img, $stream);
        rewind($stream);

        $data = stream_get_contents($stream);
        fclose($stream);

        return $data;
    }

    public function blackAndWhite(string $fileContent): string
    {
        $img = imagecreatefromstring($fileContent);
        imagefilter($img, IMG_FILTER_GRAYSCALE);
        imagefilter($img, IMG_FILTER_CONTRAST, -100);

        $stream = fopen('php://memory','r+');
        imagepng($img, $stream);
        rewind($stream);

        $data = stream_get_contents($stream);
        fclose($stream);

        return $data;
    }
}
