<?php

namespace App\Validators;

use App\Validators\RecordValidator;

class ProductValidator extends RecordValidator
{
  protected array $schema = [
    'gtin' => ['required', 'string'],
    'title' => ['required', 'string'],
    'language' => ['required', 'string', 'max:5'],
    'description' => ['string', 'nullable'],
    'picture' => ['url', 'nullable'],
    'price' => ['required', 'numeric', 'min:0'],
    'stock' => ['numeric', 'integer', 'min:0']
  ];
}