<?php

namespace App\Services;

use App\Imports\ProductsImport;
use App\Models\Upload;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Repositories\UploadRepositoryInterface;
use App\Jobs\ProcessUpload;

class ImportService implements ImportServiceInterface
{
    /**
     * @var UploadRepositoryInterface
     */
    private $uploads;

    public function __construct(UploadRepositoryInterface $uploads)
    {
        $this->uploads = $uploads;
    }

    public function create(array $params): Upload
    {
        $file = $params['file'];
        $fileName = $file->getClientOriginalName();
        $filePath = $file->store();

        $upload = $this->uploads->create([
            'filename' => $fileName,
            'filepath' => $filePath
        ]);

        ProcessUpload::dispatch($upload);

        return $upload;
    }

    public function process(Upload $upload)
    {
        Excel::import(new ProductsImport($upload), $upload->filepath);
    }
}