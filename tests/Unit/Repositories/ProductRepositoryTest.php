<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\ProductRepository;
use App\Models\Product;
use App\Exceptions\TableNotFoundException;
use Illuminate\Support\Facades\Schema;
use Mockery;

class ProductRepositoryTest extends TestCase
{
    private $mockProduct;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockProduct = Mockery::mock(Product::class);
        $this->repository = new ProductRepository($this->mockProduct);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testBulkCreateWithValidTable()
    {
        $data = [
            ['gtin' => '123', 'title' => 'Product 1'],
            ['gtin' => '456', 'title' => 'Product 2']
        ];

        $this->mockProduct->shouldReceive('getTable')
            ->once()
            ->andReturn('products');

        Schema::shouldReceive('hasTable')
            ->once()
            ->with('products')
            ->andReturn(true);

        $this->mockProduct->shouldReceive('insert')
            ->once()
            ->with($data)
            ->andReturn(true);

        $result = $this->repository->bulkCreate($data);
        $this->assertTrue($result);
    }

    public function testBulkCreateWithNonExistentTable()
    {
        $data = [
            ['gtin' => '123', 'title' => 'Product 1']
        ];

        $this->mockProduct->shouldReceive('getTable')
            ->once()
            ->andReturn('non_existent_table');

        Schema::shouldReceive('hasTable')
            ->once()
            ->with('non_existent_table')
            ->andReturn(false);

        $this->expectException(TableNotFoundException::class);
        $this->expectExceptionMessage('Table non_existent_table does not exist');

        $this->repository->bulkCreate($data);
    }

    public function testBulkCreateWithEmptyData()
    {
        $this->mockProduct->shouldReceive('getTable')
            ->once()
            ->andReturn('products');

        Schema::shouldReceive('hasTable')
            ->once()
            ->with('products')
            ->andReturn(true);

        $this->mockProduct->shouldReceive('insert')
            ->once()
            ->with([])
            ->andReturn(true);

        $result = $this->repository->bulkCreate([]);
        $this->assertTrue($result);
    }

    public function testBulkCreateWithDatabaseError()
    {
        $data = [
            ['gtin' => '123', 'title' => 'Product 1']
        ];

        $this->mockProduct->shouldReceive('getTable')
            ->once()
            ->andReturn('products');

        Schema::shouldReceive('hasTable')
            ->once()
            ->with('products')
            ->andReturn(true);

        $this->mockProduct->shouldReceive('insert')
            ->once()
            ->with($data)
            ->andReturn(false);

        $result = $this->repository->bulkCreate($data);
        $this->assertFalse($result);
    }
}