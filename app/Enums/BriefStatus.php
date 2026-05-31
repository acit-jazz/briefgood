<?php

namespace App\Enums;

enum BriefStatus: string
{
    case New = 'new';
    case Reviewing = 'reviewing';
    case Assigned = 'assigned';
    case Pitching = 'pitching';
    case Won = 'won';
    case Lost = 'lost';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Reviewing => 'Reviewing',
            self::Assigned => 'Assigned',
            self::Pitching => 'Pitching',
            self::Won => 'Won',
            self::Lost => 'Lost',
            self::Rejected => 'Rejected',
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
