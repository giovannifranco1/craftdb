<?php

namespace App\Support;

use App\Livewire\Data\ColumnData;
use App\Models\Column;
use App\Models\Diagram;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;

class Helpers
{
    public static function getIconDiamondByTypeColumn(Column|ColumnData $column): Htmlable|string
    {
        if ($column->is_primary) {
            $iconComponent = '<flux:icon.key variant="solid" class="size-3 text-amber-400"/>';
        }
        elseif ($column->is_foreign && $column->is_notnull) {
            $iconComponent = '<flux:icon.circle-small variant="solid" class="size-2.5 text-red-400"/>';
        }
        elseif ($column->is_foreign) {
            $iconComponent = '<flux:icon.circle-small class="size-2.5 text-red-400"/>';
        }

        elseif ($column->is_notnull) {
            $iconComponent = '<flux:icon.circle-small variant="solid" class="size-2.5 text-blue-300"/>';
        }
        else {
            $iconComponent = '<flux:icon.circle-small class="size-2.5 text-blue-300"/>';
        }

        return Blade::render(<<<blade
            <div class="w-3 h-3 text-red-500 rounded text-xs flex items-center justify-center font-bold">
                {$iconComponent}
            </div>
        blade);
    }

    /**
     * @param Diagram $diagram
     * @return array{int, int}
     */
    public static function getPositionCanvasByDiagram(Diagram $diagram): array
    {
        $firstTable = $diagram->tables->first();

        if (!$firstTable) {
            return [0, 0];
        }

        $marginX = 700;
        $marginY = 500;

        $x = (int) ($firstTable->x ?? 0);
        $y = (int) ($firstTable->y ?? 0);

        $offsetX = $x - $marginX;
        $offsetY = $y - $marginY;

        return [$offsetX, $offsetY];
    }

    public static function getRandomColor(string $style): string
    {
        $colors = match($style) {
            'neutral' => config('color.neutral', []),
            'colorful' => config('color.colorful', []),
            default => [],
        };

        // Convert to indexed array to work with array_rand
        $colors = array_values($colors);
        
        return $colors[array_rand($colors)];
    }

    public static function isColorValid(string $color): bool
    {
        $colorful = array_values(config('color.colorful', []));
        $neutral = array_values(config('color.neutral', []));
        
        return in_array($color, array_merge($colorful, $neutral));
    }

    /**
     * Convert hex color to rgba with opacity
     */
    public static function hexToRgba(string $hex, float $opacity = 0.1): string
    {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$opacity})";
    }

    /**
     * Calculate grid position with random offset for diagram tables
     *
     * @param int $index The index of the item (1-based)
     * @param int $maxPerRow Maximum items per row
     * @param int $spacingX Horizontal spacing between items
     * @param int $spacingY Vertical spacing between items
     * @param int $offsetX Maximum random offset on X axis
     * @param int $offsetY Maximum random offset on Y axis
     * @return array{x: int, y: int}
     */
    public static function calculateGridPosition(
        int $index,
        int $maxPerRow = 5,
        int $spacingX = 500,
        int $spacingY = 400,
        int $offsetX = 22,
        int $offsetY = 20
    ): array {
        $row = (int) floor(($index - 1) / $maxPerRow);
        $col = ($index - 1) % $maxPerRow;

        // Use deterministic random seed based on index for consistent positioning
        mt_srand($index * 2157 + 123);
        $dx = mt_rand(-$offsetX, $offsetX);
        $dy = mt_rand(-$offsetY, $offsetY);

        $x = $col * $spacingX + $dx;
        $y = $row * $spacingY + $dy;

        return ['x' => $x, 'y' => $y];
    }

    /**
     * Calculate the next order value based on the current maximum order
     *
     * @param int|float|null $maxOrder The current maximum order value
     * @param int $defaultOrder Default order value when max is null (default: 100)
     * @param int $minOrder Minimum order value (default: 1)
     * @return int The next order value to use
     */
    public static function calculateNextOrder(
        int|float|null $maxOrder,
        int $defaultOrder = 0,
        int $minOrder = 1
    ): int {
        if ($maxOrder === null) {
            return $defaultOrder;
        }

        return max($minOrder, (int) ($maxOrder + 1));
    }
}
