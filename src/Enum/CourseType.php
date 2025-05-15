<?php
namespace App\Enum;

enum CourseType: int
{
    case FREE = 0;
    case RENT = 1;
    case BUY = 2;

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'free',
            self::RENT => 'rent',
            self::BUY => 'buy',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        return match ($label) {
            'free' => self::FREE,
            'rent' => self::RENT,
            'buy' => self::BUY,
            default => null,
        };
    }
}
