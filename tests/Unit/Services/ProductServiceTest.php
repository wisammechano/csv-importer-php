<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ProductService;
use App\Repositories\ProductRepository;
use App\Validators\ProductValidator;
use Mockery;

class ProductServiceTest extends TestCase
{
  private $mockRepository;
  private $mockValidator;
  private $productService;

  protected function setUp(): void
  {
    parent::setUp();
    $this->mockRepository = Mockery::mock(ProductRepository::class);
    $this->mockValidator = Mockery::mock(ProductValidator::class);
    $this->productService = new ProductService(
      $this->mockRepository,
      $this->mockValidator
    );
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  public function testConstructorProperlyInjectsDependencies()
  {
    $this->assertInstanceOf(
      ProductService::class,
      $this->productService,
      'ProductService should be instantiated successfully'
    );

    // Test that repository is properly set
    $reflection = new \ReflectionClass($this->productService);
    $repositoryProperty = $reflection->getParentClass()->getProperty('repository');
    $repositoryProperty->setAccessible(true);

    $this->assertSame(
      $this->mockRepository,
      $repositoryProperty->getValue($this->productService),
      'Repository should be properly injected'
    );

    // Test that validator is properly set
    $validatorProperty = $reflection->getParentClass()->getProperty('validator');
    $validatorProperty->setAccessible(true);

    $this->assertSame(
      $this->mockValidator,
      $validatorProperty->getValue($this->productService),
      'Validator should be properly injected'
    );
  }

  public function testProductServiceInheritsImportServiceBehavior()
  {
    $this->assertInstanceOf(
      \App\Services\ImportService::class,
      $this->productService,
      'ProductService should extend ImportService'
    );
  }
}