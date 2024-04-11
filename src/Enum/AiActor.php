<?php

namespace App\Enum;

enum AiActor: string
{
    case System = 'system';
    case User = 'user';
    case Assistant = 'assistant';
}
