<?php

namespace App\Enums;

enum ContentStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }


    public static function options(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::INACTIVE->value => 'Inactive',
        ];
    }
}
