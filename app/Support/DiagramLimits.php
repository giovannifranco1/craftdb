<?php

namespace App\Support;

use App\Models\User;

class DiagramLimits
{
    /**
     * Get diagram limits for a user based on their plan (free/pro/team).
     */
    /**
     * @return array{diagram_limit: int, table_limit: int, table_columns_limit: int}
     */
    public static function forUser(User $user): array
    {
        if ($user->isSuperRoot()) {
            return [
                'diagram_limit' => PHP_INT_MAX,
                'table_limit' => PHP_INT_MAX,
                'table_columns_limit' => PHP_INT_MAX,
            ];
        }

        $plan = self::planKey($user);
        $planConfig = config("diagram.{$plan}", config('diagram.free'));

        return [
            'diagram_limit' => self::resolveLimit($planConfig['diagram_limit'] ?? 3),
            'table_limit' => self::resolveLimit($planConfig['table_limit'] ?? 30),
            'table_columns_limit' => self::resolveLimit($planConfig['table_columns_limit'] ?? 50),
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

    private static function resolveLimit(int|string|null $value): int
    {
        if ($value === 'unlimited' || $value === null) {
            return PHP_INT_MAX;
        }
        return (int) $value;
    }
}
