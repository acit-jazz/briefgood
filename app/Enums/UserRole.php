<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case GroupAdmin = 'group_admin';
    case BusinessUnitPic = 'business_unit_pic';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::GroupAdmin => 'Group Admin',
            self::BusinessUnitPic => 'Business Unit PIC',
            self::Viewer => 'Viewer',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
