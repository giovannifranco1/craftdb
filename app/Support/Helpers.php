<?php

namespace App\Support;

use App\Models\Column;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;

class Helpers
{
    public static function getIconDiamondByTypeColumn(Column $column): Htmlable|string
    {
        // Primary key (nullable) - blue background
        if ($column->is_primary) {
            $iconComponent = '<flux:icon.circle-small variant="solid" class="size-2.5 text-amber-400"/>';
        }

        // Foreign key (nullable) - red solid
        elseif ($column->is_foreign && $column->is_nullable) {
            $iconComponent = '<flux:icon.circle-small variant="solid" class="size-2.5 text-red-400"/>';
        }
        // Foreign key (not nullable) - red outline
        elseif ($column->is_foreign) {
            $iconComponent = '<flux:icon.circle-small class="size-2.5 text-red-400"/>';
        }

        elseif ($column->is_nullable) {
            $iconComponent = '<flux:icon.circle-small variant="solid" class="size-2.5 text-blue-300"/>';
        }
        // Default case
        else {
            $iconComponent = '<flux:icon.circle-small class="size-2.5 text-blue-300"/>';
        }

        // Renderiza o template Blade com o componente
        return Blade::render(<<<blade
            <div class="w-3 h-3 text-red-500 rounded text-xs flex items-center justify-center font-bold">
                {$iconComponent}
            </div>
        blade);
    }
}
