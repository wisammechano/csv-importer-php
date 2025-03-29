<?php

namespace App\Factories;

use App\Services\ImportService;
use App\Services\ProductService;
use InvalidArgumentException;

class ImportServiceFactory
{
  public function make(string $table): ImportService
  {
    return match ($table) {
      'products' => app(ProductService::class),
      default => throw new InvalidArgumentException("Unsupported table: {$table}"),
    };
  }
}