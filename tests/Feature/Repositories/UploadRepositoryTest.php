<?php

namespace Tests\Feature\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\UploadRepositoryInterface;
use App\Models\Upload;

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
        $upload = $this->repository->create([
            'filename' => fake()->slug() .'.csv',
            'status' => fake()->randomElement(Upload::STATUSES)
        ]);

        $this->assertInstanceOf(Upload::class, $upload);
        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload->getKey(),
            'filename' => $upload->filename
        ]);

    }

    public function test_update_status()
    {
        $upload = Upload::factory()->create();

        $updatedUpload = $this->repository->updateStatus($upload->getKey(), Upload::STATUS_COMPLETED);

        $this->assertInstanceOf(Upload::class, $updatedUpload);
        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload->getKey(),
            'status' => Upload::STATUS_COMPLETED
        ]);
    }
}