<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\MessageBag;

class ValidationException extends Exception
{
  /**
   * The validator errors.
   *
   * @var MessageBag
   */
  protected $errors;

  /**
   * Create a new validation exception instance.
   *
   * @param MessageBag|array $errors
   */
  public function __construct($errors)
  {
    $this->errors = $errors;
    parent::__construct('Invalid data provided.');
  }

  /**
   * Get the validation errors.
   *
   * @return MessageBag
   */
  public function errors()
  {
    return $this->errors;
  }
}
