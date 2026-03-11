<?php

namespace App\Enums;

enum BillingCountry: string
{
    case USD = 'USD';
    case BRL = 'BRL';

    /**
     * Parse country code from CF-IPCountry header or string.
     * Returns US as default when not BR.
     */
    public static function fromRequest(?string $header = null): self
    {
        $code = strtoupper(trim($header ?? ''));

        return match ($code) {
            'BRL' => self::BRL,
            default => self::USD,
        };
    }

    /**
     * Plan keys available for this country.
     */
    public function planKeys(): array
    {
        return match ($this) {
            self::USD => ['pro_usd', 'team_usd'],
            self::BRL => ['pro_brl', 'team_brl'],
        };
    }

    /**
     * Currency symbol for display.
     */
    public function currencySymbol(): string
    {
        return match ($this) {
            self::USD => '$',
            self::BRL => 'R$',
        };
    }

    /**
     * Whether the given plan key is valid for this country.
     */
    public function hasPlan(string $planKey): bool
    {
        return in_array($planKey, $this->planKeys(), true);
    }
}
