<?php

namespace App\Http\Controllers;

use App\Contracts\ImportServiceInterface;
use App\Http\Requests\UploadRequest;
use Illuminate\Http\Request;
use App\Transformers\UploadTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use Inertia\Inertia;
use App\Contracts\UploadRepositoryInterface;

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

    /**
     * @var UploadRepositoryInterface
     */
    private $repository;

    public function __construct(
        ImportServiceInterface $service,
        UploadTransformer $transformer,
        UploadRepositoryInterface $repository
    ) {
        $this->service = $service;
        $this->transformer = $transformer;
        $this->repository = $repository;
    }

    public function upload(UploadRequest $request)
    {
        if ($request->validated()) {

            $upload = $this->service->create($request->all());

            return response()->json([
                'data' => $this->transformer->transform($upload)
            ]);
        }

        return response(null, 400);
    }

    public function index()
    {
        $uploads = $this->repository->getAll();

        return Inertia::render('Home', [
            'uploads' => $uploads->toArray()
        ]);
    }
}
