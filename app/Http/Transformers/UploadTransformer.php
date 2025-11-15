<?php

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract;
use App\Models\Upload;
use Carbon\Carbon;

class UploadTransformer extends TransformerAbstract
{
    public function transform(Upload $upload)
    {
        return [
            'id' => $upload->id,
            'filename' => $upload->filename,
            'status' => $upload->status,
            'created_at' => $upload->created_at
        ];
    }
}