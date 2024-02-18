<?php

use App\Kernel;

try {
    require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

    return function (array $context) {
        return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    };
} catch (\Symfony\Component\ErrorHandler\Error\FatalError) {
    // ignore that piece of crap
}
