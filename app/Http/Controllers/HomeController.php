<?php

namespace App\Http\Controllers;

use App\Services\ImportServiceInterface;
use App\Http\Requests\UploadRequest;
use Illuminate\Http\Request;
use App\Http\Transformers\UploadTransformer;

class HomeController extends Controller
{
    /**
     * @var ImportServiceInterface
     */
    private $service;

    /**
     * @var UploadTransformer
     */
    private $transformer;

    public function __construct(ImportServiceInterface $service, UploadTransformer $transformer)
    {
        $this->service = $service;
        $this->transformer = $transformer;
    }

    public function upload(UploadRequest $request)
    {
        if ($request->validated()) {

            return response()->json([
                'data' => $this->transformer->transform($this->service->create($request->all()))
            ]);
        }

        return response(null, 400);
    }
}
