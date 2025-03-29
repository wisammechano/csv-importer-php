<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use MongoDB\Laravel\Eloquent\Model;

/**
 * Base model class that serves as a foundation for all models in the application.
 * This class extends Laravel's Model class but can be easily modified to use MongoDB
 * by changing the parent class to MongoDB\Laravel\Eloquent\Model when needed.
 * 
 * This abstraction allows for flexible database backend switching without modifying
 * individual model classes.
 */
class BaseModel extends Model
{
}
