<?php

namespace App\Enums;

enum RefundReason: string
{
    case DamagedItem = 'damaged_item';
    case IncorrectItem = 'incorrect_item';
    case NotAsDescribed = 'not_as_described';
    case ChangedMind = 'changed_mind';
    case NoLongerNeeded = 'no_longer_needed';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DamagedItem => 'Item arrived damaged',
            self::IncorrectItem => 'Wrong item received',
            self::NotAsDescribed => 'Item not as described',
            self::ChangedMind => 'Changed my mind',
            self::NoLongerNeeded => 'No longer needed',
            self::Other => 'Other',
        };
    }
}
