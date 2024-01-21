<?php

namespace App\Service;

final readonly class Transliterator
{
    public function transliterate(string $text): string
    {
        return transliterator_transliterate('NFKC; Any-Latin; Latin-ASCII;', $text);
    }
}
