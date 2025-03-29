<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;

abstract class RecordValidator
{
  private array $errors = [];
  protected array $schema = [];

  /**
   * Validates an array of records against the given schema
   *
   * @param array $records Array of records to validate
   * @param array $schema Validation rules array defining constraints for each field
   * @return array Array containing validation results with errors if any
   */
  public function validateBulk(array $records): array
  {
    $this->errors = [];
    $validatedRecords = [];

    foreach ($records as $index => $record) {
      $validator = Validator::make($record, $this->schema);

      if ($validator->fails()) {
        $this->errors[$index] = $validator->errors();
      } else {
        $validatedRecords[] = $validator->validated();
      }
    }

    return $validatedRecords;
  }

  /**
   * Gets validation errors from the last validation
   *
   * @return array Array of validation errors
   */
  public function getErrors(): array
  {
    return $this->errors;
  }
}