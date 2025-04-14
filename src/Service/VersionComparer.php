<?php

namespace App\Service;

final readonly class VersionComparer
{
    public function compare(string $left, string $right): int
    {
        if ($left === 'dev' && $right === 'dev') {
            return 0;
        }
        if ($left === 'dev') {
            return 1;
        }
        if ($right === 'dev') {
            return -1;
        }

        return version_compare($left, $right);
    }
}
