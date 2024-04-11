<?php

namespace App\Enum;

enum RunConfiguration: string
{
    case Always = 'always';
    case WhenNotAborted = 'not_aborted';
}
