<?php

// app/Services/ProductService.php

namespace App\Services;

use App\Services\ImportService;
use App\Repositories\ProductRepository;
use App\Validators\ProductValidator;

class ProductService extends ImportService
{
  public function __construct(
    ProductRepository $repository,
    ProductValidator $validator
  ) {
    $this->repository = $repository;
    $this->validator = $validator;
  }
}
