<?php

namespace App\Environment;

use Closure;
use DateInterval;
use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

final readonly class DateIntervalEnvironmentProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, Closure $getEnv): DateInterval
    {
        $value = $getEnv($name);
        return new DateInterval($value);
    }

    public static function getProvidedTypes(): array
    {
        return [
            'interval' => 'string',
        ];
    }
}
