<?php

namespace App\Enums;

enum LicenseStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return [
            self::ACTIVE->value => 'Active',
            self::EXPIRED->value => 'Expired',
            self::REVOKED->value => 'Revoked',
        ];
    }
}
