<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Upload;
use App\Services\ImportServiceInterface;

class ProcessUpload implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Upload $upload
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ImportServiceInterface $service): void
    {
        $service->process($this->upload);
    }
}
