<?php

namespace Tests\Feature\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\UploadRepositoryInterface;
use App\Models\Upload;
use Illuminate\Support\Collection;

class UploadRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var UploadRepositoryInterface
     */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(UploadRepositoryInterface::class);
    }

    public function test_create()
    {
        $fileName = fake()->slug(2) .'.csv';
        $filePath = fake()->md5() .'.csv';

        $upload = $this->repository->create([
            'filename' => $fileName,
            'filepath' => $filePath,
            'status' => fake()->randomElement(Upload::STATUSES)
        ]);

        $this->assertInstanceOf(Upload::class, $upload);
        $this->assertDatabaseHas(Upload::getTableName(), [
            'filename' => $upload->filename,
            'filepath' => $upload->filepath
        ]);

    }

    public function test_get_all()
    {
        Upload::factory(3)->create();

        $uploads = $this->repository->getAll();

        $this->assertInstanceOf(Collection::class, $uploads);

        foreach ($uploads as $upload)
            $this->assertInstanceOf(Upload::class, $upload);
    }
}