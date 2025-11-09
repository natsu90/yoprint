<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Upload;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class ProductsImport implements ToModel, WithUpserts, 
    WithHeadingRow, WithEvents
{
    use RegistersEventListeners;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $dbColumnMap = [
            'unique_key' => 'id',
            'product_title' => 'title',
            'product_description' => 'description',
            'style' => 'style',
            'sanmar_mainframe_color' => 'mainframe_color',
            'size' => 'size',
            'color_name' => 'color',
            'piece_price' => 'price'
        ];

        foreach ($row as $header => $value)
        {
            if (isset($dbColumnMap[$header])) {
                $dbColumn = $dbColumnMap[$header];
                $dataToUpsert[$dbColumn] = $value;
            }
        }

        return new Product($dataToUpsert);

        // return new Product([
        //     'id' => $row['unique_key'],
        //     'title' => $row['product_title'] ?? null,
        //     'description' => $row['product_description'] ?? null,
        //     'style' => $row['style'] ?? null,
        //     'mainframe_color' => $row['sanmar_mainframe_color'] ?? null,
        //     'size' => $row['size'] ?? null,
        //     'color' => $row['color_name'] ?? null,
        //     'price' => $row['piece_price'] ?? null
        // ]);
    }

    /**
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'id';
    }

    public function beforeImport(BeforeImport $event)
    {
        $this->upload->update([
            'status' => Upload::STATUS_PROCESSING
        ]);
    }

    public function afterImport(AfterImport $event)
    {
        $this->upload->update([
            'status' => Upload::STATUS_COMPLETED
        ]);
    }

    public function importFailed(ImportFailed $event)
    {
        $this->upload->update([
            'status' => Upload::STATUS_FAILED
        ]);
    }
}
