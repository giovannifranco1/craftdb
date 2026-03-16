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
            'BR' => self::BRL,
            default => self::USD,
        };
    }

    /**
     * Plan keys available for this country.
     */
    public function planKeys(): array
    {
        return match ($this) {
            self::USD => ['pro_usd', 'pro_usd_annual', 'team_usd', 'team_usd_annual'],
            self::BRL => ['pro_brl', 'pro_brl_annual', 'team_brl', 'team_brl_annual'],
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
