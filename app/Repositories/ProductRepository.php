<?php

namespace App\Repositories;

use App\Contracts\IRepository;
use App\Exceptions\TableNotFoundException;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class ProductRepository implements IRepository
{
  protected $model;

  public function __construct(Product $product)
  {
    $this->model = $product;
  }

  public function bulkCreate(array $data): bool
  {
    $this->assertTableExists();

    return $this->model->insert($data);  // Bulk insert
  }

  private function assertTableExists(): void
  {
    $table = $this->model->getTable();
    // No SQL Injection risk as $table is fetched from model not user input
    if (!Schema::hasTable($table)) {
      throw new TableNotFoundException("Table $table does not exist");
    }
  }
}
