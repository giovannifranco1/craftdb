<?php

namespace App\Observers;

use App\Models\Diagram;
use Illuminate\Support\Facades\Cache;

class DiagramObserver
{
    /**
     * Handle the Diagram "updated" event.
     *
     * Clear the cache when a diagram is updated so that
     * the next request will fetch fresh data.
     */
    public function updated(Diagram $diagram): void
    {
        $this->clearDiagramCache($diagram);
    }

    /**
     * Handle the Diagram "deleted" event.
     *
     * Clear the cache when a diagram is deleted.
     */
    public function deleted(Diagram $diagram): void
    {
        $this->clearDiagramCache($diagram);
    }

    /**
     * Clear the diagram cache from all cache stores.
     */
    private function clearDiagramCache(Diagram $diagram): void
    {
        $cacheKey = "diagram:slug:{$diagram->slug}";

        // Clear from both array (per-request) and default cache stores
        Cache::store('array')->forget($cacheKey);
        Cache::forget($cacheKey);
    }
}
