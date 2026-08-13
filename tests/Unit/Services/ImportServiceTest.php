<?php

namespace Tests\Unit\Services;

use App\Contracts\UploadRepositoryInterface;
use App\Models\Upload;
use App\Services\ExcelImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    private $repositoryMock;

    private ExcelImportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = $this->mock(UploadRepositoryInterface::class);
        $this->service = new ExcelImportService($this->repositoryMock);
    }

    public function test_create_new_upload(): void
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

    public function test_create_new_upload_first_chunk_does_not_process(): void
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

    public function test_create_appends_to_existing_upload(): void
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

    public function test_create_processes_on_last_chunk(): void
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
