<?php

namespace App\Support;

use App\Models\User;

class DiagramLimits
{
    /**
     * Get diagram limits for a user based on their plan (free/pro/team).
     */
    public static function forUser(User $user): array
    {
        $plan = static::planKey($user);
        $planConfig = config("diagram.{$plan}", config('diagram.free'));

        return [
            'diagram_limit' => static::resolveLimit($planConfig['diagram_limit'] ?? 3),
            'table_limit' => static::resolveLimit($planConfig['table_limit'] ?? 30),
            'table_columns_limit' => static::resolveLimit($planConfig['table_columns_limit'] ?? 50),
        ];
    }

    private static function planKey(User $user): string
    {
        if ($user->isTeamPlan()) {
            return 'team';
        }
        if ($user->isPro()) {
            return 'pro';
        }
        return 'free';
    }

    private static function resolveLimit(int|string $value): int
    {
        if ($value === 'unlimited' || $value === null) {
            return PHP_INT_MAX;
        }
        return (int) $value;
    }
}
