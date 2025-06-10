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
    public function code(): int
    {
        return match ($this) {
            self::RENT => 1,
            self::FREE => 0,
            self::BUY => 2,
        };
    }
    public static function byCode(int $code): CourseType
    {
        return match ($code) {
            1 => self::RENT,
            0 => self::FREE,
            2 => self::BUY,
        };
    }
    public function title(): string
    {
        return match ($this) {
            self::RENT => 'Аренда',
            self::FREE => 'Бесплатный',
            self::BUY => 'Платный',
        };
    }
    public static function byString(string $value): CourseType
    {
        return match ($value) {
            'rent' => self::RENT,
            'free' => self::FREE,
            'buy' => self::BUY,
        };
    }
}
