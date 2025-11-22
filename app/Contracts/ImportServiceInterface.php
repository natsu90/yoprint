<?php

namespace App\Contracts;

use App\Models\Upload;

interface ImportServiceInterface
{
    /**
     * Create an Upload record
     */
    public function create(array $params): Upload;

    /**
     * Process Uploaded CSV and update Product records
     * 
     * @param Upload $upload
     * @return Collection
     */
    public function process(Upload $upload);
}