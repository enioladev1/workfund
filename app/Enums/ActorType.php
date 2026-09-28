<?php

namespace App\Enums;

enum ActorType: string
{
    case System = 'system';
    case Customer = 'customer';
    case Staff = 'staff';
}
