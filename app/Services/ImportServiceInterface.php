<?php

namespace App\Services;

use App\Models\Upload;

interface ImportServiceInterface
{
    /**
     * Process Uploaded CSV and update Product records
     * 
     * @param Upload $upload
     * @return Collection
     */
    public function process(Upload $upload);
}