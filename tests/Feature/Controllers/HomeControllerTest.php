<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Upload;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Excel::fake();
    }

    public function testUpload()
    {
        $content = file_get_contents(base_path('tests/data/yoprint_test_updated.csv'));
        $file = UploadedFile::fake()->createWithContent('test.csv', $content);

        $response = $this->post('/upload', [
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'filename',
                    'status',
                    'created_at'
                ]
            ]);

        $data = json_decode($response->getContent())->data;
        $uploadId = $data->id;
        $upload = Upload::find($uploadId);

        Excel::assertQueued($upload->filepath);
    }
}