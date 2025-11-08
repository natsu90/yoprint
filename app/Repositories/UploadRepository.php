<?php

namespace App\Repositories;

use App\Models\Upload;

class UploadRepository implements UploadRepositoryInterface
{
    public function create(array $params): Upload
    {
        return Upload::create($params);
    }

    public function updateStatus(int $uploadId, string $status): Upload
    {
        $upload = Upload::find($uploadId);

        $upload->status = $status;
        $upload->save();

        return $upload;
    }
}