<?php

namespace Tests\Feature\Repositories;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Repositories\ProductRepositoryInterface;
use App\Models\Product;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var ProductRepositoryInterface
     */
    private $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(ProductRepositoryInterface::class);
    }

    public function test_create()
    {
        $params = Product::factory()->make()->toArray();
        $product = $this->repository->updateOrCreate($params);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => $params['id'],
            'title' => $params['title'],
            'description' => $params['description']
        ]);
    }

    public function test_update()
    {
        $product = Product::factory()->create();

        $updatedProduct = $this->repository->updateOrCreate([
            'id' => $product->getKey(),
            'title' => fake()->sentence()
        ]);

        $this->assertInstanceOf(Product::class, $updatedProduct);
        $this->assertDatabaseHas(Product::getTableName(), [
            'id' => $product->getKey(),
            'title' => $updatedProduct->title
        ]);
    }
}