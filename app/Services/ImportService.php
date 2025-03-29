<?php

namespace App\Services;

use App\Contracts\IRepository;
use App\Validators\RecordValidator;
use App\Exceptions\ValidationException;

abstract class ImportService
{
  protected IRepository $repository;
  protected RecordValidator $validator;

  /**
   * Import records into the repository
   * 
   * @param array $records Array of records to be imported
   * @return bool Returns true if import was successful, false otherwise
   */
  public function import(array $records): bool
  {
    $validatedData = $this->validator->validateBulk($records);
    $validationErrors = $this->validator->getErrors();

    if (count($validationErrors) > 0) {
      throw new ValidationException($validationErrors);
    }

    if (count($validatedData) > 0) {
      return $this->repository->bulkCreate($validatedData);
    }

    return false;
  }
}
