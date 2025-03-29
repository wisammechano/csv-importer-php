<?php

namespace Tests\Unit\Services;

use App\Services\CsvReaderService;
use League\Csv\Exception as CsvException;
use Tests\TestCase;
use Generator;

class CsvReaderServiceTest extends TestCase
{
    private CsvReaderService $csvReader;
    private string $testFilePath;
    private string $testFilePathCustomDelimeter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->csvReader = new CsvReaderService();

        // Create a temporary CSV file for testing
        $this->testFilePath = sys_get_temp_dir() . '/test.csv';
        $this->testFilePathCustomDelimeter = sys_get_temp_dir() . '/test.tsv';
        $testData = "id,name,email,phone\n1,John Doe,john@example.com,123-4567\n2,Jane Smith,,555-1234\n3,Bob Johnson,bob@example.com,\n4,,anonymous@example.com,999-8888";
        $testDataTabs = "id	name	email	phone\n1	John Doe	john@example.com	123-4567\n2	Jane Smith		555-1234\n3	Bob Johnson	bob@example.com	\n4		anonymous@example.com	999-8888";
        file_put_contents($this->testFilePath, $testData);
        file_put_contents($this->testFilePathCustomDelimeter, $testDataTabs);
    }

    protected function tearDown(): void
    {
        // Clean up the temporary file
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
        parent::tearDown();
    }

    public function testReadReturnsGenerator()
    {
        $result = $this->csvReader->read($this->testFilePath);
        $this->assertInstanceOf(Generator::class, $result);
    }

    public function testReadWithDefaultChunkSize()
    {
        $chunks = iterator_to_array($this->csvReader->read($this->testFilePath));

        $this->assertCount(1, $chunks); // All records in one chunk as total records < CHUNK_SIZE
        $this->assertCount(4, $chunks[0]); // 4 records in total

        // Verify first record
        $this->assertEquals([
            'id' => '1',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => "123-4567"
        ], $chunks[0][0]);
    }

    public function testReadWithCustomChunkSize()
    {
        $chunks = iterator_to_array($this->csvReader->read($this->testFilePath, 2));

        $this->assertCount(2, $chunks); // Should split into 2 chunks (2 records each)
        $this->assertCount(2, $chunks[0]); // First chunk has 2 records
        $this->assertCount(2, $chunks[1]); // Second chunk has 2 records
    }

    public function testReadWithEmptyFile()
    {
        // Create empty CSV file with headers
        $emptyFilePath = sys_get_temp_dir() . '/empty.csv';
        file_put_contents($emptyFilePath, "id,name,email,phone\n");

        $chunks = iterator_to_array($this->csvReader->read($emptyFilePath));

        $this->assertCount(0, $chunks); // No chunks for empty file

        unlink($emptyFilePath);
    }

    public function testReadWithInvalidFile()
    {
        $this->expectException(CsvException::class);
        iterator_to_array($this->csvReader->read('nonexistent.csv'));
    }

    public function testReadPreservesHeaderAssociation()
    {
        $chunks = iterator_to_array($this->csvReader->read($this->testFilePath, 1));

        foreach ($chunks as $chunk) {
            foreach ($chunk as $record) {
                $this->assertArrayHasKey('id', $record);
                $this->assertArrayHasKey('name', $record);
                $this->assertArrayHasKey('email', $record);
                $this->assertArrayHasKey('phone', $record);
            }
        }
    }

    public function testReadWithSingleRecordChunks()
    {
        $chunks = iterator_to_array($this->csvReader->read($this->testFilePath, 1));

        $this->assertCount(4, $chunks); // Should have 4 chunks of 1 record each
        foreach ($chunks as $chunk) {
            $this->assertCount(1, $chunk);
        }
    }

    public function testReadEmptyFieldsAsNull()
    {
        $chunks = iterator_to_array($this->csvReader->read($this->testFilePath));

        // Verify second record email is null
        $this->assertEquals(null, $chunks[0][1]['email']);
        // Verify third record phone is null
        $this->assertEquals(null, $chunks[0][2]['phone']);
        // Verify fourth record name is null
        $this->assertEquals(null, $chunks[0][3]['name']);

    }

    public function testReadCustomDelimiter()
    {
        $chunks = iterator_to_array($this->csvReader->read($this->testFilePathCustomDelimeter, delimiter: "\t"));

        $this->assertCount(1, $chunks); // All records in one chunk as total records < CHUNK_SIZE
        $this->assertCount(4, $chunks[0]); // 4 records in total

        // Verify first record
        $this->assertEquals([
            'id' => '1',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => "123-4567"
        ], $chunks[0][0]);
    }
}