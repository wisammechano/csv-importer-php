<?php

namespace Tests\Unit\Validators;

use Tests\TestCase;
use App\Validators\RecordValidator;

class RecordValidatorTest extends TestCase
{
  private $validator;

  protected function setUp(): void
  {
    parent::setUp();

    // Create concrete implementation of abstract class for testing
    $this->validator = new class extends RecordValidator {
      protected array $schema = [
        'name' => ['required', 'string'],
        'age' => ['required', 'integer', 'min:0']
      ];
    };
  }

  public function testValidateBulkWithValidRecords()
  {
    $records = [
      ['name' => 'John Doe', 'age' => 25],
      ['name' => 'Jane Doe', 'age' => 30]
    ];

    $validatedRecords = $this->validator->validateBulk($records);

    $this->assertCount(2, $validatedRecords);
    $this->assertEquals($records, $validatedRecords);
    $this->assertEmpty($this->validator->getErrors());
  }

  public function testValidateBulkWithInvalidRecords()
  {
    $records = [
      ['name' => '', 'age' => -1],
      ['name' => 'Jane Doe', 'age' => 30],
      ['age' => 25]
    ];

    $validatedRecords = $this->validator->validateBulk($records);

    $this->assertCount(1, $validatedRecords);
    $this->assertEquals(['name' => 'Jane Doe', 'age' => 30], $validatedRecords[0]);

    $errors = $this->validator->getErrors();
    $this->assertCount(2, $errors);
    $this->assertArrayHasKey(0, $errors);
    $this->assertArrayHasKey(2, $errors);
  }

  public function testValidateBulkWithEmptyRecords()
  {
    $records = [];

    $validatedRecords = $this->validator->validateBulk($records);

    $this->assertEmpty($validatedRecords);
    $this->assertEmpty($this->validator->getErrors());
  }
}