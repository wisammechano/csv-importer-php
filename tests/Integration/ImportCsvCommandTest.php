<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class ImportCsvCommandTest extends TestCase
{
  protected function setUp(): void
  {
    parent::setUp();

    // Ensure we're using the test database
    config(['database.default' => 'sqlite_testing']);

    // Run migrations
    $this->artisan('migrate:fresh');
  }

  protected function tearDown(): void
  {
    // Clean up test database
    Schema::dropIfExists('products');
    parent::tearDown();
  }

  public function testImportValidCsvFile()
  {
    // Create a test CSV file
    $csvContent = "gtin,title,language,price,stock\n";
    $csvContent .= "12345,Test Product,en,99.99,100\n";
    $csvContent .= "67890,Another Product,es,149.99,50";

    $testFile = tempnam(sys_get_temp_dir(), 'csv_test');
    file_put_contents($testFile, $csvContent);

    // Run the import command
    $this->artisan("import:csv $testFile --table=products")
      ->expectsOutput('Imported 2 records successfully.')
      ->assertExitCode(0);

    // Verify database records
    $products = DB::table('products')->get();
    $this->assertCount(2, $products);

    $firstProduct = $products->first();
    $this->assertEquals('12345', $firstProduct->gtin);
    $this->assertEquals('Test Product', $firstProduct->title);
    $this->assertEquals(99.99, $firstProduct->price);

    unlink($testFile);
  }

  public function testImportInvalidData()
  {
    // Create a CSV file with invalid data
    $csvContent = "gtin,title,language,price,stock\n";
    $csvContent .= ",Invalid Product,en,-99.99,-50\n"; // Invalid GTIN and negative values

    $testFile = tempnam(sys_get_temp_dir(), 'csv_test');
    file_put_contents($testFile, $csvContent);

    // Capture logs while running command
    $this->artisan("import:csv $testFile --table=products")
      ->expectsOutput('No records were imported.')
      ->assertExitCode(0);

    // Verify no records were inserted
    $this->assertEquals(0, DB::table('products')->count());

    unlink($testFile);
  }

  public function testImportMultipleFiles()
  {
    // Create two test CSV files
    $csv1Content = "gtin,title,language,price,stock\n12345,Product 1,en,99.99,100";
    $csv2Content = "gtin,title,language,price,stock\n67890,Product 2,en,149.99,50";

    $testFile1 = tempnam(sys_get_temp_dir(), 'csv_test1');
    $testFile2 = tempnam(sys_get_temp_dir(), 'csv_test2');

    file_put_contents($testFile1, $csv1Content);
    file_put_contents($testFile2, $csv2Content);

    $this->artisan("import:csv $testFile1 $testFile2 --table=products")
      ->expectsOutput('Imported 2 records successfully.')
      ->assertExitCode(0);

    $this->assertEquals(2, DB::table('products')->count());

    unlink($testFile1);
    unlink($testFile2);
  }

  public function testImportWithChunking()
  {
    // Create CSV with more than chunk size records
    $csvContent = "gtin,title,language,price,stock\n";
    for ($i = 1; $i <= 150; $i++) {
      $csvContent .= sprintf("%05d,Product %d,en,99.99,100\n", $i, $i);
    }

    $testFile = tempnam(sys_get_temp_dir(), 'csv_test');
    file_put_contents($testFile, $csvContent);

    $this->artisan("import:csv $testFile --table=products")
      ->expectsOutput('Imported 150 records successfully.')
      ->assertExitCode(0);

    $this->assertEquals(150, DB::table('products')->count());

    unlink($testFile);
  }
}