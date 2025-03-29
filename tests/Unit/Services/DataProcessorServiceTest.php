<?php

namespace Tests\Unit\Services;

use App\Services\DataProcessorService;
use Tests\TestCase;

class DataProcessorServiceTest extends TestCase
{
  private DataProcessorService $processor;

  protected function setUp(): void
  {
    parent::setUp();
    $this->processor = new DataProcessorService();
  }

  public function testProcessEmptyArray()
  {
    $result = $this->processor->process([]);
    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  public function testProcessSingleRecord()
  {
    $record = [
      'name' => '  John Doe  ',
      'email' => 'john@example.com ',
      'notes' => '',
      'special@field' => 'value'
    ];

    $expected = [
      'name' => 'John Doe',
      'email' => 'john@example.com',
      'notes' => null,
      'specialfield' => 'value'
    ];

    $result = $this->processor->process([$record]);
    $this->assertEquals([$expected], $result);
  }

  public function testProcessMultipleRecords()
  {
    $records = [
      [
        'name' => '  Alice  ',
        'age' => ' 25 '
      ],
      [
        'name' => ' Bob ',
        'age' => ' 30 '
      ]
    ];

    $expected = [
      [
        'name' => 'Alice',
        'age' => '25'
      ],
      [
        'name' => 'Bob',
        'age' => '30'
      ]
    ];

    $result = $this->processor->process($records);
    $this->assertEquals($expected, $result);
  }

  public function testXSSPrevention()
  {
    $record = [
      'name' => '<script>alert("XSS")</script>',
      'description' => 'Normal text with <tags> & special "chars"'
    ];

    $result = $this->processor->process([$record])[0];

    $this->assertStringNotContainsString('<script>', $result['name']);
    $this->assertEquals(
      '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;',
      $result['name']
    );
  }

  public function testSpecialCharactersInKeys()
  {
    $record = [
      'first-name' => 'John',
      'last_name' => 'Doe',
      'email@address' => 'john@example.com'
    ];

    $result = $this->processor->process([$record])[0];

    $this->assertArrayHasKey('firstname', $result);
    $this->assertArrayHasKey('last_name', $result);
    $this->assertArrayHasKey('emailaddress', $result);
  }

  public function testNonStringValues()
  {
    $record = [
      'age' => 25,
      'active' => true,
      'score' => 99.9,
      'data' => null
    ];

    $result = $this->processor->process([$record])[0];

    $this->assertSame(25, $result['age']);
    $this->assertSame(true, $result['active']);
    $this->assertSame(99.9, $result['score']);
    $this->assertNull($result['data']);
  }

  public function testEmptyStringsConvertedToNull()
  {
    $record = [
      'field1' => '',
      'field2' => '  ',
      'field3' => 'not empty'
    ];

    $result = $this->processor->process([$record])[0];

    $this->assertNull($result['field1']);
    $this->assertNull($result['field2']);
    $this->assertEquals('not empty', $result['field3']);
  }
}