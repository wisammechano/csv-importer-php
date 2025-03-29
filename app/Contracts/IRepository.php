<?php

namespace App\Contracts;

/**
 * Repository Contract Interface
 * 
 * This interface defines the contract for repository classes that handle
 * data persistence operations. It provides methods for bulk creation
 * of records in the underlying storage system.
 */
interface IRepository
{
  /**
   * Create multiple records in the repository
   *
   * @param array $data Array of data to be inserted into the repository
   * @return bool True if the records were created successfully, false otherwise
   */
  public function bulkCreate(array $data): bool;
}