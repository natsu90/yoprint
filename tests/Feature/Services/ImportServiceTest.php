<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\ImportServiceInterface;
use App\Models\Upload;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ImportServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ImportServiceInterface::class);
    }

    public function testProcess()
    {
        // create an Upload record
        $upload = Upload::factory()->create();

        // confirm Pending status
        $this->assertEquals($upload->status, Upload::STATUS_PENDING);

        // put file content
        $content = file_get_contents(base_path('tests/data/yoprint_test_updated.csv'));
        $filePath = $upload->filepath;
        Storage::put($filePath, $content);
        Storage::assertExists($filePath);

        $this->service->process($upload);

        $this->assertDatabaseCount(Product::getTableName(), 17);

        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload->getKey(),
            'status' => Upload::STATUS_COMPLETED
        ]);

        // delete file after test
        Storage::delete($filePath);
        Storage::assertMissing($filePath);
    }

    public function testProcessDuplicate()
    {
        // create two Upload records
        $upload1 = Upload::factory()->create();
        $upload2 = Upload::factory()->create();

        // put file content
        $content1 = file_get_contents(base_path('tests/data/yoprint_test_updated.csv'));
        $filePath1 = $upload1->filepath;
        Storage::put($filePath1, $content1);

        $newDescription = md5(time());
        $content2 = "UNIQUE_KEY,PRODUCT_TITLE\n62822,". $newDescription;
        $filePath2 = $upload2->filepath;
        Storage::put($filePath2, $content2);

        // process Uploads
        $this->service->process($upload1);
        $this->service->process($upload2);

        $this->assertDatabaseCount(Product::getTableName(), 17);
        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => 62822,
            'title' => $newDescription,
            'color' => 'White' // confirm that old value is not updated
        ]);

        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload1->getKey(),
            'status' => Upload::STATUS_COMPLETED
        ]);
        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload2->getKey(),
            'status' => Upload::STATUS_COMPLETED
        ]);

        // delete file after test
        Storage::delete($filePath1);
        Storage::delete($filePath2);
        Storage::assertMissing($filePath1);
        Storage::assertMissing($filePath2);
    }
}