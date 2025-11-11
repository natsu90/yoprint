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
            'filename' => $upload->filename,
            'status' => $upload->status,
            'uploaded_at' => $upload->created_at
        ];
    }
}