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

    public function getAll(): Collection
    {
        return Upload::orderBy('created_at', 'desc')->get();
    }
}