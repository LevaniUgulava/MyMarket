<?php

namespace App\Enums;

enum Status: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Canceled = 'canceled';
}
