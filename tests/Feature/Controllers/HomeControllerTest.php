<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
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
                    'filename',
                    'status',
                    'uploaded_at'
                ]
            ]);
    }
}