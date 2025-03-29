<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_IMPORT_DRIVER', 'sqlite'),
    'available_connections' => ['sqlite', 'postgres', 'mongodb'],

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('SQLITE_URL'),
            'database' => env('SQLITE_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('SQLITE_FOREIGN_KEYS', true),
        ],
        'sqlite_testing' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ],
        'postgres' => [
            'driver' => 'pgsql',
            'url' => env('POSTGRES_URL'),
            'host' => env('POSTGRES_HOST', '127.0.0.1'),
            'port' => env('POSTGRES_PORT', '5432'),
            'database' => env('POSTGRES_DB', 'postgres'),
            'username' => env('POSTGRES_USER', 'root'),
            'password' => env('POSTGRES_PASSWORD', ''),
            'charset' => env('POSTGRES_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],
        'mongodb' => [
            'driver' => 'mongodb',
            'dsn' => env('MONGO_URI'),
            // 'host' => env('MONGO_HOSTT', '127.0.0.1'),
            // 'port' => env('MONGO_PORT', 27017),
            // 'database' => env('MONGO_DB', 'mongo'),
            // 'username' => env('MONGO_INITDB_ROOT_USERNAME', 'root'),
            // 'password' => env('MONGO_INITDB_ROOT_PASSWORD', ''),
            'options' => [
                // here you can pass more settings to the Mongo Driver Manager
                // see https://www.php.net/manual/en/mongodb-driver-manager.construct.php under "Uri Options" for a list of complete parameters that you can use

                'database' => env('DB_AUTHENTICATION_DATABASE', 'admin'), // required with Mongo 3+
            ],
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

];
