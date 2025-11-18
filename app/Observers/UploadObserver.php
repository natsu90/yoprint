<?php

namespace App\Observers;

use App\Models\Upload;
use App\Events\UploadUpdated;

class UploadObserver
{
    /**
     * Handle the Upload "created" event.
     */
    public function created(Upload $upload): void
    {
        //
    }

    /**
     * Handle the Upload "updated" event.
     */
    public function updated(Upload $upload): void
    {
        if ($upload->wasChanged(['status', 'processed', 'total'])) {
            UploadUpdated::dispatch($upload);
        }
    }

    /**
     * Handle the Upload "deleted" event.
     */
    public function deleted(Upload $upload): void
    {
        //
    }

    /**
     * Handle the Upload "restored" event.
     */
    public function restored(Upload $upload): void
    {
        //
    }

    /**
     * Handle the Upload "force deleted" event.
     */
    public function forceDeleted(Upload $upload): void
    {
        //
    }
}
