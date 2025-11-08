<?php

namespace App\Repositories;

use App\Models\Upload;

interface UploadRepositoryInterface
{
    /**
     * Create a new Upload record
     * 
     * @param array $params
     * @return Upload
     */
    public function create(array $params): Upload;

    /**
     * Update status of an Upload record
     * 
     * @param int $uploadId
     * @param string $status
     * @return Upload
     */
    public function updateStatus(int $uploadId, string $status): Upload;
}