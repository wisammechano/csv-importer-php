<?php

namespace App\Services;

use League\Csv\Reader;
use Generator;

class CsvReaderService
{
    private const CHUNK_SIZE = 500;

    /**
     * Reads a CSV file in chunks to manage memory usage for large files
     *
     * @param string $fp The path to the CSV file to be read
     * @param int $chunkSize The number of records to read per chunk. Default 500
     * @return Generator A generator yielding arrays of records, with each array containing CHUNK_SIZE records (or less for the final chunk)
     * @throws \League\Csv\Exception When the CSV file cannot be read or is invalid
     *
     * @example
     * $reader = new CsvReaderService();
     * foreach ($reader->read('path/to/file.csv', 1000) as $chunk) {
     *     // Process chunk of records
     * }
     */
    public function read(string $fp, int $chunkSize = self::CHUNK_SIZE, string $delimiter = ","): Generator
    {
        $csv = Reader::createFromPath($fp, 'r');
        $csv->setHeaderOffset(0);
        $csv->setDelimiter($delimiter);

        // Memory-efficient reading
        $records = $csv->getRecords();
        $chunk = [];
        $count = 0;

        foreach ($records as $record) {
            $chunk[] = $record;
            $count++;

            if ($count === $chunkSize) {
                yield $chunk;
                $chunk = [];
                $count = 0;
            }
        }

        // Yield remaining records if any
        if (!empty($chunk)) {
            yield $chunk;
        }
    }
}
