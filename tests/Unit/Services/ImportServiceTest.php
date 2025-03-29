<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\ImportService;
use App\Contracts\IRepository;
use App\Validators\RecordValidator;
use App\Exceptions\ValidationException;
use Mockery;

class ImportServiceTest extends TestCase
{
  private $mockRepository;
  private $mockValidator;
  private $importService;

  protected function setUp(): void
  {
    parent::setUp();

    $this->mockRepository = Mockery::mock(IRepository::class);
    $this->mockValidator = Mockery::mock(RecordValidator::class);

    // Create a concrete implementation of the abstract class for testing
    $this->importService = new class ($this->mockRepository, $this->mockValidator) extends ImportService {
      public function __construct($repository, $validator)
      {
        $this->repository = $repository;
        $this->validator = $validator;
      }
    };
  }

  protected function tearDown(): void
  {
    Mockery::close();
    parent::tearDown();
  }

  public function testSuccessfulImport()
  {
    $records = [
      ['name' => 'Test1'],
      ['name' => 'Test2']
    ];

    $this->mockValidator->shouldReceive('validateBulk')
      ->once()
      ->with($records)
      ->andReturn($records);

    $this->mockValidator->shouldReceive('getErrors')
      ->once()
      ->andReturn([]);

    $this->mockRepository->shouldReceive('bulkCreate')
      ->once()
      ->with($records)
      ->andReturn(true);

    $result = $this->importService->import($records);
    $this->assertTrue($result);
  }

  public function testImportWithValidationErrors()
  {
    $records = [
      ['name' => ''],
      ['name' => 'Test2']
    ];

    $validationErrors = [
      0 => ['name' => ['Name is required']]
    ];

    $this->mockValidator->shouldReceive('validateBulk')
      ->once()
      ->with($records)
      ->andReturn([]);

    $this->mockValidator->shouldReceive('getErrors')
      ->once()
      ->andReturn($validationErrors);

    $this->expectException(ValidationException::class);

    $this->importService->import($records);
  }

  public function testImportWithEmptyRecords()
  {
    $records = [];

    $this->mockValidator->shouldReceive('validateBulk')
      ->once()
      ->with($records)
      ->andReturn([]);

    $this->mockValidator->shouldReceive('getErrors')
      ->once()
      ->andReturn([]);

    $result = $this->importService->import($records);
    $this->assertFalse($result);
  }

  public function testImportWithNoValidRecords()
  {
    $records = [
      ['name' => 'Test1'],
      ['name' => 'Test2']
    ];

    $this->mockValidator->shouldReceive('validateBulk')
      ->once()
      ->with($records)
      ->andReturn([]);

    $this->mockValidator->shouldReceive('getErrors')
      ->once()
      ->andReturn([]);

    $result = $this->importService->import($records);
    $this->assertFalse($result);
  }

  public function testImportWithRepositoryFailure()
  {
    $records = [
      ['name' => 'Test1'],
      ['name' => 'Test2']
    ];

    $this->mockValidator->shouldReceive('validateBulk')
      ->once()
      ->with($records)
      ->andReturn($records);

    $this->mockValidator->shouldReceive('getErrors')
      ->once()
      ->andReturn([]);

    $this->mockRepository->shouldReceive('bulkCreate')
      ->once()
      ->with($records)
      ->andReturn(false);

    $result = $this->importService->import($records);
    $this->assertFalse($result);
  }
}