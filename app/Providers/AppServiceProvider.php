<?php

namespace App\Providers;

use App\Contracts\DataImportContract;
use App\Models\BaseModel;
use App\Services\MongoDBImportService;
use App\Services\DBImportService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model as MongoModel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
