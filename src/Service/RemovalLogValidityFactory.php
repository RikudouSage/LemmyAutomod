<?php

namespace App\Service;

use DateInterval;
use LogicException;

final readonly class RemovalLogValidityFactory
{
    public static function createLogValidity(string $validity): ?DateInterval
    {
        if (is_numeric($validity)) {
            if (!$validity) {
                return null;
            }

            return new DateInterval("PT{$validity}H");
        }

        if (str_starts_with($validity, 'P')) {
            return new DateInterval($validity);
        }

        throw new LogicException("Unsupported format for validity: {$validity}");
    }
}
