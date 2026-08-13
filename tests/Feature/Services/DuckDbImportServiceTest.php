<?php

namespace Tests\Feature\Services;

use App\Events\UploadUpdated;
use App\Models\Product;
use App\Models\Upload;
use App\Services\DuckDbImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Saturio\DuckDB\DuckDB;
use Tests\TestCase;
use Throwable;

class DuckDbImportServiceTest extends TestCase
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
     * @var DuckDbImportService
     */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DuckDB::create();
        } catch (Throwable $exception) {
            $this->markTestSkipped('DuckDB C library is not available: '.$exception->getMessage());
        }

        $this->service = $this->app->make(DuckDbImportService::class);

        // https://github.com/laravel/framework/issues/18923#issuecomment-1470106626
        Event::fake(UploadUpdated::class);
    }

    public function test_process()
    {
        // create an Upload record
        $upload = Upload::factory()->create();

        // confirm Pending status
        $this->assertEquals($upload->status, Upload::STATUS_UPLOADING);

        $this->assertDatabaseMissing(Product::getTableName(), [
            'id' => self::PRODUCT_ID_TEST,
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
            'total' => self::PRODUCT_TOTAL_TEST,
            'processed' => self::PRODUCT_TOTAL_TEST,
            'status' => Upload::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => self::PRODUCT_ID_TEST,
            'color' => self::PRODUCT_COLOR_TEST,
            'style' => self::PRODUCT_STYLE_TEST,
        ]);

        // one event when the row count is known and processing starts, one when
        // the last batch has been upserted. Every event carries the same Upload
        // instance, so only its final state can be asserted on
        Event::assertDispatchedTimes(UploadUpdated::class, 2);

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload) {
            return $event->upload->id === $upload->id
                && $event->upload->processed === self::PRODUCT_TOTAL_TEST
                && $event->upload->status === Upload::STATUS_COMPLETED;
        });

        // delete file after test
        Storage::delete($filePath);
        Storage::assertMissing($filePath);
    }

    public function test_process_import_file()
    {
        $upload = Upload::factory()->create();

        $this->assertEquals($upload->status, Upload::STATUS_UPLOADING);

        $content = file_get_contents(base_path('tests/data/yoprint_test_import.csv'));
        $filePath = $upload->filepath;
        Storage::put($filePath, $content);
        Storage::assertExists($filePath);

        $this->service->process($upload);

        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload->getKey(),
            'status' => Upload::STATUS_COMPLETED,
        ]);

        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => 62822,
        ]);

        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => 1563841,
        ]);

        // Verify long color values (>30 chars) are stored without truncation
        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => 780621,
            'color' => 'Dark Grey/ Black/ Sport Fuchsia',
        ]);

        Storage::delete($filePath);
        Storage::assertMissing($filePath);
    }

    public function test_process_duplicate()
    {
        // create two Upload records
        $upload1 = Upload::factory()->create();
        $upload2 = Upload::factory()->create();

        // put file content
        $content1 = file_get_contents(base_path(self::PRODUCT_FILE_PATH));
        $filePath1 = $upload1->filepath;
        Storage::put($filePath1, $content1);

        $newTitle = md5(time());
        $content2 = "UNIQUE_KEY,PRODUCT_TITLE\n".self::PRODUCT_ID_TEST.','.$newTitle;
        $filePath2 = $upload2->filepath;
        Storage::put($filePath2, $content2);

        // process Uploads
        $this->service->process($upload1);
        $this->service->process($upload2);

        // confirm that products total is remaining the same even when processed twice
        $this->assertDatabaseCount(Product::getTableName(), self::PRODUCT_TOTAL_TEST);

        // confirm that only title was updated, the columns missing from the
        // second file are left untouched
        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => self::PRODUCT_ID_TEST,
            'title' => $newTitle,
            'color' => self::PRODUCT_COLOR_TEST,
            'style' => self::PRODUCT_STYLE_TEST,
        ]);

        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload1->getKey(),
            'status' => Upload::STATUS_COMPLETED,
        ]);
        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload2->getKey(),
            'status' => Upload::STATUS_COMPLETED,
        ]);

        // delete file after test
        Storage::delete($filePath1);
        Storage::delete($filePath2);
        Storage::assertMissing($filePath1);
        Storage::assertMissing($filePath2);
    }

    public function test_process_without_unique_key_column_fails()
    {
        $upload = Upload::factory()->create();

        $filePath = $upload->filepath;
        Storage::put($filePath, "PRODUCT_TITLE,COLOR_NAME\nSome Title,White");

        try {

            $this->service->process($upload);

            $this->fail('Expected a RuntimeException for a file without a UNIQUE_KEY column.');

        } catch (RuntimeException $exception) {

            $this->assertStringContainsString('UNIQUE_KEY', $exception->getMessage());
        }

        $this->assertDatabaseCount(Product::getTableName(), 0);

        $this->assertDatabaseHas(Upload::getTableName(), [
            'id' => $upload->getKey(),
            'status' => Upload::STATUS_FAILED,
        ]);

        Event::assertDispatched(UploadUpdated::class, function ($event) use ($upload) {
            return $event->upload->id === $upload->id
                && $event->upload->status === Upload::STATUS_FAILED;
        });

        Storage::delete($filePath);
        Storage::assertMissing($filePath);
    }

    public function test_process_missing_file_fails()
    {
        $upload = Upload::factory()->create();

        $this->expectException(Throwable::class);

        try {

            $this->service->process($upload);

        } finally {

            $this->assertDatabaseHas(Upload::getTableName(), [
                'id' => $upload->getKey(),
                'status' => Upload::STATUS_FAILED,
            ]);
        }
    }
}
