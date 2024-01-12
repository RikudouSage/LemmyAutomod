<?php

namespace App\Automod\Enum;

enum AutomodPriority: int
{
    case Default = 0;
    case Notification = -1_000;
}
