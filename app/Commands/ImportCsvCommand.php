<?php

namespace App\Commands;

use App\Exceptions\TableNotFoundException;
use App\Factories\ImportServiceFactory;
use App\Services\DataProcessorService;
use App\Services\ImportService;
use App\Exceptions\ValidationException;
use App\Services\CsvReaderService;
use Exception;
use Illuminate\Support\Facades\Log;
use LaravelZero\Framework\Commands\Command;
use PDOException;


class ImportCsvCommand extends Command
{
    private static array $tables = [
        'products',
    ];

    protected CsvReaderService $csvReader;
    protected ImportService $importer;

    protected DataProcessorService $processor;

    public function __construct(CsvReaderService $service, DataProcessorService $processor)
    {
        parent::__construct();
        $this->csvReader = $service;
        $this->processor = $processor;
    }

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'import:csv 
                           {files*}
                           {--table= : The db table or collection to use}';
    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Import a CSV files for processing';

    private function parseArguments(): array
    {
        $files = $this->argument('files');
        $table = $this->option('table');

        // Validate files
        if (count($files) == 0) {
            $this->error('No files specified.');
            return [];
        }

        $validFiles = [];

        foreach ($files as $file) {
            if (!file_exists($file)) {
                $this->warn("Ignoring not found file: $file");
                continue;
            }

            $validFiles[] = $file;
        }

        if (count($validFiles) == 0) {
            $this->warn("No files to process.");
            return [];
        }

        // Validate model
        $validTables = self::$tables;
        if (!in_array($table, $validTables)) {
            $this->error('Invalid table specified. Available tables: ' . implode(', ', $validTables));
            return [];
        }

        return [
            'files' => $validFiles,
            'table' => $table,
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle(ImportServiceFactory $svcFactory): int
    {
        $args = $this->parseArguments();
        if (count($args) == 0) {
            // Arguments errors are printed already. Exit early
            return 1;
        }

        $files = $args['files'];
        $table = $args['table'];

        // Log some info
        $driver = $this->app->config->get("database.default");
        $this->info("Using database driver: $driver");
        $this->info("Using table: $table");
        $this->info("Importing " . count($files) . " csv files");

        Log::debug("Importing csv files: " . implode(', ', $files));
        Log::debug("Using database driver: $driver");

        // Instantiate service
        Log::debug("Instantiating importing service for table: $table");
        $this->importer = $svcFactory->make($table);

        try {
            $processed = 0;
            foreach ($files as $file) {
                $processed += $this->processFile($file);
            }

            if ($processed > 0) {
                $this->info("Imported $processed records successfully.");
            } else {
                $this->warn("No records were imported.");
            }

            // Success
            Log::debug("Imported $processed records successfully.");
            return 0;
        } catch (TableNotFoundException $e) {
            $this->error($e->getMessage() . ". Make sure you run the migrations.");
            Log::debug($e->getMessage() . ". Possibly migrations are not run.");
            return 1;
        } catch (PDOException $e) {
            $this->error("A database error happened. Check the log for more details");
            Log::debug($e);
            return 1;
        } catch (Exception $e) {
            $this->error('An unexpected error happened. Check the log for more details');
            Log::debug($e);
            return 1;
        }
    }

    private function processFile($file): int
    {
        $CHUNK_SIZE = 100;

        Log::debug("Reading csv file in chunks of $CHUNK_SIZE rows: $file");
        $chunks = $this->csvReader->read($file, $CHUNK_SIZE);

        $processed = 0;
        foreach ($chunks as $chunk) {
            $chunkIdx = intdiv($processed, $CHUNK_SIZE) + 1;
            Log::debug("Processing chunk $chunkIdx");
            try {
                // Sanitize
                $chunk = $this->processor->process($chunk);

                // Validate & Persist
                $success = $this->importer->import($chunk);
                if ($success) {
                    $processed += count($chunk);
                }
            } catch (ValidationException $e) {
                $errors = $e->errors();
                foreach ($errors as $line => $err) {
                    $msg = "Row " . $line + $processed + 1 . ":\n";
                    $msg .= collect($err->all())->map(fn($error) => "  - {$error}")->implode("\n");
                    $this->warn($msg);
                }
                $this->error("Invalid data provided in file $file. Chunk $chunkIdx. Skipping chunk.");
                Log::debug("Invalid data provided in file $file. Chunk $chunkIdx of count " . count($chunk) . ". Chunk size: $CHUNK_SIZE");
            }
        }
        return $processed;
    }
}
