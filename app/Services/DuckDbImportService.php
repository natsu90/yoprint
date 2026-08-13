<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Upload;
use ForceUTF8\Encoding;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Result\ResultSet;
use Throwable;

class DuckDbImportService extends AbstractImportService
{
    /**
     * CSV heading (normalised) to products table column
     *
     * @var array<string, string>
     */
    private const COLUMN_MAP = [
        'unique_key' => 'id',
        'product_title' => 'title',
        'product_description' => 'description',
        'style' => 'style',
        'sanmar_mainframe_color' => 'mainframe_color',
        'size' => 'size',
        'color_name' => 'color',
        'piece_price' => 'price',
    ];

    /**
     * Rows upserted per query
     */
    private const BATCH_SIZE = 1000;

    public function process(Upload $upload)
    {
        try {
            $duckDb = DuckDB::create();
            $csvSource = $this->csvSource(Storage::path($upload->filepath));
            $columnMap = $this->columnMap($duckDb, $csvSource);

            if (! in_array('id', $columnMap, true)) {
                throw new RuntimeException("Upload {$upload->getKey()} has no UNIQUE_KEY column to upsert on.");
            }

            $upload->update([
                'total' => $this->totalRows($duckDb, $csvSource),
                'status' => Upload::STATUS_PROCESSING,
            ]);

            $updateColumns = array_values(array_diff($columnMap, ['id']));
            $rows = $duckDb->query($this->selectQuery($columnMap, $csvSource));

            $this->upsertProducts($upload, $rows, $updateColumns);

        } catch (Throwable $exception) {

            $upload->update(['status' => Upload::STATUS_FAILED]);

            throw $exception;
        }
    }

    /**
     * @param  array<int, string>  $updateColumns
     */
    private function upsertProducts(Upload $upload, ResultSet $rows, array $updateColumns): void
    {
        $batch = [];
        $processed = 0;

        foreach ($rows->rows(columnNameAsKey: true) as $row) {
            $batch[] = array_map(
                fn ($value) => is_string($value) ? Encoding::fixUTF8($value) : $value,
                $row
            );
            $processed++;

            if (count($batch) < self::BATCH_SIZE) {
                continue;
            }

            Product::upsert($batch, ['id'], $updateColumns);
            $upload->update(['processed' => $processed]);

            $batch = [];
        }

        if (! empty($batch)) {
            Product::upsert($batch, ['id'], $updateColumns);
        }

        $upload->update([
            'processed' => $processed,
            'status' => Upload::STATUS_COMPLETED,
        ]);
    }

    /**
     * DuckDB table function reading the uploaded CSV as text, so values are
     * upserted exactly as they were written
     */
    private function csvSource(string $filePath): string
    {
        return sprintf(
            "read_csv('%s', header = true, all_varchar = true)",
            str_replace("'", "''", $filePath)
        );
    }

    /**
     * Headings actually present in the CSV, mapped to their products table column
     *
     * @return array<string, string>
     */
    private function columnMap(DuckDB $duckDb, string $csvSource): array
    {
        $result = $duckDb->query(sprintf('SELECT * FROM %s LIMIT 0', $csvSource));

        $columnMap = [];

        foreach ($result->columnNames() as $heading) {
            $normalised = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($heading)), '_');

            if (isset(self::COLUMN_MAP[$normalised])) {
                $columnMap[$heading] = self::COLUMN_MAP[$normalised];
            }
        }

        return $columnMap;
    }

    /**
     * @param  array<string, string>  $columnMap
     */
    private function selectQuery(array $columnMap, string $csvSource): string
    {
        $selects = [];

        foreach ($columnMap as $heading => $dbColumn) {
            $selects[] = sprintf('"%s" AS "%s"', str_replace('"', '""', $heading), $dbColumn);
        }

        return sprintf('SELECT %s FROM %s', implode(', ', $selects), $csvSource);
    }

    private function totalRows(DuckDB $duckDb, string $csvSource): int
    {
        $result = $duckDb->query(sprintf('SELECT count(*) FROM %s', $csvSource));

        foreach ($result->rows() as $row) {
            return (int) $row[0];
        }

        return 0;
    }
}
