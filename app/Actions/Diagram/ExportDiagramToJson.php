<?php

namespace App\Actions\Diagram;

use App\Models\Diagram;

class ExportDiagramToJson
{
    /**
     * @return array<string, mixed>
     */
    public function exec(Diagram $diagram): array
    {
        $diagram->loadMissing('tables.columns');

        $relationships = $diagram->relationships()
            ->with(['fromTable', 'fromColumn', 'toTable', 'toColumn'])
            ->get();

        return [
            'craftdb_format' => 'full',
            'version'        => '1.0',
            'exported_at'    => now()->toIso8601String(),
            'diagram'        => [
                'name'        => $diagram->name,
                'style'       => $diagram->style,
                'database'    => $diagram->database,
                'description' => $diagram->description,
            ],
            'tables' => $diagram->tables->map(fn ($table) => [
                'name'    => $table->name,
                'columns' => $table->columns->sortBy('order')->values()->map(fn ($col) => [
                    'name'          => $col->name,
                    'type'          => $col->type,
                    'is_primary'    => (bool) $col->is_primary,
                    'is_foreign'    => (bool) $col->is_foreign,
                    'is_notnull'    => (bool) $col->is_notnull,
                    'order'         => $col->order,
                    'length'        => $col->length,
                    'default_value' => $col->default_value,
                    'comment'       => $col->comment,
                ])->all(),
            ])->all(),
            'relationships' => $relationships->map(fn ($rel) => [
                'from_table'  => $rel->fromTable?->name,
                'from_column' => $rel->fromColumn?->name,
                'to_table'    => $rel->toTable?->name,
                'to_column'   => $rel->toColumn?->name,
                'type'        => $rel->type->value,
                'label'       => $rel->label,
            ])->all(),
        ];
    }
}
