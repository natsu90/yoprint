<?php

namespace App\Repositories;

use App\Models\Product;

interface ProductRepositoryInterface 
{
    /**
     * Create or update a Product
     * 
     * @param array $params
     * @return Product
     */
    public function updateOrCreate(array $params): Product;
}