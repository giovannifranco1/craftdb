<?php

namespace App\Observers;

use App\Models\Table;

class TableObserver
{
    /**
     * Handle the Table "created" event.
     */
    public function created(Table $table): void
    {
        $table->diagram->update([
            'updated_at' => now(),
        ]);
    }

    /**
     * Handle the Table "updated" event.
     */
    public function updated(Table $table): void
    {
        $table->diagram->update([
            'updated_at' => now(),
        ]);
    }

    /**
     * Handle the Table "deleted" event.
     */
    public function deleted(Table $table): void
    {
        $table->diagram->update([
            'updated_at' => now(),
        ]);
    }

    /**
     * Handle the Table "restored" event.
     */
    public function restored(Table $table): void
    {
        $table->diagram->update([
            'updated_at' => now(),
        ]);
    }
}
