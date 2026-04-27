<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Upload;
use App\Contracts\ImportServiceInterface;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Excel::fake();

        $this->serviceMock = $this->mock(ImportServiceInterface::class);
        $this->app->instance(ImportServiceInterface::class, $this->serviceMock);
    }

    public function testUpload()
    {
        $upload = Upload::factory()->create();
        $file = UploadedFile::fake()->createWithContent('test.csv', file_get_contents(base_path('tests/data/yoprint_test_updated.csv')));

        $this->serviceMock->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(fn ($params) => isset($params['file']) && !isset($params['append_file'])))
            ->andReturn($upload);

        $response = $this->post('/upload', [
            'file' => $file,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'filename',
                    'status',
                    'created_at',
                ],
            ]);
    }

    public function testUploadExisting()
    {
        $upload = Upload::factory()->create();
        $uploadId = $upload->getKey();
        $file = UploadedFile::fake()->createWithContent('test.csv', file_get_contents(base_path('tests/data/yoprint_test_updated.csv')));

        $this->serviceMock->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(fn ($params) => isset($params['append_file']) && $params['append_file'] == $uploadId && empty($params['last_append'])))
            ->andReturn($upload);

        $response = $this->post('/upload', [
            'file' => $file,
            'append_file' => $uploadId,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'filename',
                    'status',
                    'created_at',
                ],
            ]);
    }

    public function testUploadLastChunk()
    {
        $upload = Upload::factory()->create();
        $uploadId = $upload->getKey();
        $file = UploadedFile::fake()->createWithContent('test.csv', file_get_contents(base_path('tests/data/yoprint_test_updated.csv')));

        $this->serviceMock->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(fn ($params) => isset($params['append_file']) && $params['append_file'] == $uploadId && !empty($params['last_append'])))
            ->andReturn($upload);

        $response = $this->post('/upload', [
            'file' => $file,
            'append_file' => $uploadId,
            'last_append' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'filename',
                    'status',
                    'created_at',
                ],
            ]);
    }
}