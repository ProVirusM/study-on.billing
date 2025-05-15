<?php
namespace App\Enum;

enum TransactionType: int
{
    case DEPOSIT = 0;
    case PAYMENT = 1;

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'deposit',
            self::PAYMENT => 'payment',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        return match ($label) {
            'deposit' => self::DEPOSIT,
            'payment' => self::PAYMENT,
            default => null,
        };
    }
}
