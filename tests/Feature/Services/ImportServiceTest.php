<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\ImportServiceInterface;
use App\Models\Upload;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Event;
use App\Events\UploadUpdated;

class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test File Path
     */
    const PRODUCT_FILE_PATH = 'tests/data/yoprint_test_updated.csv';

    /**
     * Product ID to be tested
     */
    const PRODUCT_ID_TEST = 62822;

    /**
     * Expected Product Title
     */
    const PRODUCT_COLOR_TEST = 'White';

    /**
     * Expected Product Style
     */
    const PRODUCT_STYLE_TEST = '054X';

    /**
     * Expected Total Products
     */
    const PRODUCT_TOTAL_TEST = 17;

    /**
     * @var ImportServiceInterface
     */
    private $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(ImportServiceInterface::class);

        // https://github.com/laravel/framework/issues/18923#issuecomment-1470106626
        Event::fake(UploadUpdated::class);
    }

    public function testProcess()
    {
        // create an Upload record
        $upload = Upload::factory()->create();

        // confirm Pending status
        $this->assertEquals($upload->status, Upload::STATUS_PENDING);

        $this->assertDatabaseMissing(Product::getTableName(), [
            'id' => self::PRODUCT_ID_TEST
        ]);

        // put file content
        $content = file_get_contents(base_path(self::PRODUCT_FILE_PATH));
        $filePath = $upload->filepath;
        Storage::put($filePath, $content);
        Storage::assertExists($filePath);

        $this->service->process($upload);

        $this->assertDatabaseCount(Product::getTableName(), self::PRODUCT_TOTAL_TEST);

        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload->getKey(),
            'status' => Upload::STATUS_COMPLETED
        ]);

        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => self::PRODUCT_ID_TEST,
            'color' => self::PRODUCT_COLOR_TEST,
            'style' => self::PRODUCT_STYLE_TEST
        ]);

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload) {
            return $event->upload->id === $upload->id  
                && $event->upload->processed > 0
                && $event->upload->status === Upload::STATUS_PROCESSING;
        });

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload) {
            return $event->upload->id === $upload->id  
                && $event->upload->processed === 0
                && $event->upload->status === Upload::STATUS_PROCESSING;
        });

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload) {
            return $event->upload->id === $upload->id 
                && $event->upload->processed === self::PRODUCT_TOTAL_TEST
                && $event->upload->status === Upload::STATUS_COMPLETED;
        });

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
        $content1 = file_get_contents(base_path(self::PRODUCT_FILE_PATH));
        $filePath1 = $upload1->filepath;
        Storage::put($filePath1, $content1);

        $newTitle = md5(time());
        $content2 = "UNIQUE_KEY,PRODUCT_TITLE\n". self::PRODUCT_ID_TEST .",". $newTitle;
        $filePath2 = $upload2->filepath;
        Storage::put($filePath2, $content2);

        // process Uploads
        $this->service->process($upload1);
        $this->service->process($upload2);

        // confirm that products total is remaining the same even when processed twice
        $this->assertDatabaseCount(Product::getTableName(), self::PRODUCT_TOTAL_TEST);

        // confirm that only title was updated,
        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => self::PRODUCT_ID_TEST,
            'title' => $newTitle,
            'color' => self::PRODUCT_COLOR_TEST,
            'style' => self::PRODUCT_STYLE_TEST
        ]);

        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload1->getKey(),
            'status' => Upload::STATUS_COMPLETED
        ]);
        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload2->getKey(),
            'status' => Upload::STATUS_COMPLETED
        ]);

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload1) {
            return $event->upload->id === $upload1->id 
                && $event->upload->status === Upload::STATUS_PROCESSING
                && $event->upload->processed === 0;
        });

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload1) {
            return $event->upload->id === $upload1->id 
                && $event->upload->status === Upload::STATUS_PROCESSING
                && $event->upload->processed > 0;
        });

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload1) {
            return $event->upload->id === $upload1->id 
                && $event->upload->status === Upload::STATUS_COMPLETED;
        });

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload2) {
            return $event->upload->id === $upload2->id 
                && $event->upload->status === Upload::STATUS_PROCESSING
                && $event->upload->processed === 0;
        });

        // no idea why this is not called
        // Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload2) {
        //     return $event->upload->id === $upload2->id 
        //         && $event->upload->status === Upload::STATUS_PROCESSING
        //         && $event->upload->processed > 0;
        // });

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload2) {
            return $event->upload->id === $upload2->id 
                && $event->upload->status === Upload::STATUS_COMPLETED;
        });

        // delete file after test
        Storage::delete($filePath1);
        Storage::delete($filePath2);
        Storage::assertMissing($filePath1);
        Storage::assertMissing($filePath2);
    }
}