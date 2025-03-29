<?php

namespace App\Services;

class DataProcessorService
{
  /**
   * Process and sanitize the input records
   *
   * @param array $records Array of records to process
   * @return array Processed and sanitized records
   */
  public function process(array $records): array
  {
    return array_map(function ($record) {
      $record = $this->sanitizeRecord($record);
      // Chain more functions if needed for processing
      return $record;
    }, $records);
  }

  /**
   * Sanitize a single record
   *
   * @param array $record Single record to sanitize
   * @return array Sanitized record
   */
  private function sanitizeRecord(array $record): array
  {
    $sanitizedRecord = [];

    foreach ($record as $key => $value) {
      // Trim whitespace
      if (is_string($value)) {
        $value = trim($value);
      }
      // Convert empty strings to null
      if ($value === '') {
        $value = null;
      }

      // Remove special characters from keys
      $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);

      // Basic XSS prevention
      if (is_string($value)) {
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
      }

      $sanitizedRecord[$key] = $value;
    }

    return $sanitizedRecord;
  }
}
