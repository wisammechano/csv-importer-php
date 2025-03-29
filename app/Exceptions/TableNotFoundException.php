<?php

namespace App\Exceptions;

use Exception;

class TableNotFoundException extends Exception
{
  public function __construct(string $message = "Table not found")
  {
    parent::__construct($message);
  }
}
