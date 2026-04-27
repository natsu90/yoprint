<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ImportService;
use App\Contracts\UploadRepositoryInterface;
use App\Imports\ProductsImport;
use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ImportServiceTest extends TestCase
{
    private $repositoryMock;
    private ImportService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = $this->mock(UploadRepositoryInterface::class);
        $this->service = new ImportService($this->repositoryMock);
    }

    public function testCreateNewUpload(): void
    {
        Storage::fake();
        Excel::shouldReceive('import')->once();

        $file = UploadedFile::fake()->create('test.csv', 100, 'text/csv');
        $upload = Upload::factory()->make();

        $this->repositoryMock->shouldReceive('create')
            ->once()
            ->andReturn($upload);

        $result = $this->service->create(['file' => $file, 'last_append' => true]);

        $this->assertInstanceOf(Upload::class, $result);
    }

    public function testCreateNewUploadFirstChunkDoesNotProcess(): void
    {
        Storage::fake();
        Excel::shouldReceive('import')->never();

        $file = UploadedFile::fake()->create('test.csv', 100, 'text/csv');
        $upload = Upload::factory()->make();

        $this->repositoryMock->shouldReceive('create')
            ->once()
            ->andReturn($upload);

        $result = $this->service->create(['file' => $file]);

        $this->assertInstanceOf(Upload::class, $result);
    }

    public function testCreateAppendsToExistingUpload(): void
    {
        Storage::fake();
        Excel::shouldReceive('import')->never();

        $existingUpload = Upload::factory()->make(['id' => 1, 'filepath' => 'uploads/test.csv']);
        Storage::put($existingUpload->filepath, 'chunk1_content');

        $file = UploadedFile::fake()->createWithContent('test.csv', 'chunk2_content');

        $this->repositoryMock->shouldReceive('get')
            ->once()
            ->with($existingUpload->getKey())
            ->andReturn($existingUpload);

        $result = $this->service->create([
            'file' => $file,
            'append_file' => $existingUpload->getKey(),
        ]);

        $this->assertInstanceOf(Upload::class, $result);
        $this->assertSame('chunk1_contentchunk2_content', Storage::get($existingUpload->filepath));
    }

    public function testCreateProcessesOnLastChunk(): void
    {
        Storage::fake();
        Excel::shouldReceive('import')->once();

        $file = UploadedFile::fake()->create('test.csv', 100, 'text/csv');
        $existingUpload = Upload::factory()->make(['id' => 1, 'filepath' => 'uploads/test.csv']);
        Storage::put($existingUpload->filepath, 'id,name');

        $this->repositoryMock->shouldReceive('get')
            ->once()
            ->with($existingUpload->getKey())
            ->andReturn($existingUpload);

        $result = $this->service->create([
            'file' => $file,
            'append_file' => $existingUpload->getKey(),
            'last_append' => true,
        ]);

        $this->assertInstanceOf(Upload::class, $result);
    }
}
