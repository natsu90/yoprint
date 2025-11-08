<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository implements ProductRepositoryInterface
{
    public function updateOrCreate(array $params): Product
    {
        return Product::updateOrCreate([
            'id' => $params['id']
        ], $params);
    }
}