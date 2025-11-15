<?php

namespace App\Repositories;

use App\Models\Upload;
use Illuminate\Support\Collection;

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

    public function getAll(): Collection
    {
        return Upload::orderBy('created_at', 'desc')->get();
    }
}