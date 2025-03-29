<?php

namespace Tests\Unit\Validators;

use Tests\TestCase;
use App\Validators\ProductValidator;

class ProductValidatorTest extends TestCase
{
  private ProductValidator $validator;

  protected function setUp(): void
  {
    parent::setUp();
    $this->validator = new ProductValidator();
  }

  public function testValidateValidProduct()
  {
    $records = [
      [
        'gtin' => '1234567890123',
        'title' => 'Test Product',
        'language' => 'en',
        'description' => 'Test description',
        'picture' => 'https://example.com/image.jpg',
        'price' => 99.99,
        'stock' => 100
      ]
    ];

    $validatedRecords = $this->validator->validateBulk($records);

    $this->assertCount(1, $validatedRecords);
    $this->assertEquals($records[0], $validatedRecords[0]);
    $this->assertEmpty($this->validator->getErrors());
  }

  public function testValidateInvalidProduct()
  {
    $records = [
      [
        'gtin' => '',
        'title' => '',
        'language' => 'english',  // too long
        'picture' => 'not-a-url',
        'price' => -10,
        'stock' => -5
      ]
    ];

    $validatedRecords = $this->validator->validateBulk($records);

    $this->assertEmpty($validatedRecords);

    $errors = $this->validator->getErrors();
    $this->assertCount(1, $errors);
    $this->assertArrayHasKey(0, $errors);

    $productErrors = $errors[0]->toArray();
    $this->assertArrayHasKey('gtin', $productErrors);
    $this->assertArrayHasKey('title', $productErrors);
    $this->assertArrayHasKey('language', $productErrors);
    $this->assertArrayHasKey('picture', $productErrors);
    $this->assertArrayHasKey('price', $productErrors);
    $this->assertArrayHasKey('stock', $productErrors);
  }

  public function testValidateWithOptionalFields()
  {
    $records = [
      [
        'gtin' => '1234567890123',
        'title' => 'Test Product',
        'language' => 'en',
        'price' => 99.99,
        'stock' => 100
        // description and picture are optional
      ]
    ];

    $validatedRecords = $this->validator->validateBulk($records);

    $this->assertCount(1, $validatedRecords);
    $this->assertEquals($records[0], $validatedRecords[0]);
    $this->assertEmpty($this->validator->getErrors());
  }

  public function testValidateMultipleProducts()
  {
    $records = [
      [
        'gtin' => '1234567890123',
        'title' => 'Product 1',
        'language' => 'en',
        'price' => 99.99,
        'stock' => 100
      ],
      [
        'gtin' => '1234567890124',
        'title' => 'Product 2',
        'language' => 'es',
        'price' => 149.99,
        'stock' => 50
      ]
    ];

    $validatedRecords = $this->validator->validateBulk($records);

    $this->assertCount(2, $validatedRecords);
    $this->assertEquals($records, $validatedRecords);
    $this->assertEmpty($this->validator->getErrors());
  }
}