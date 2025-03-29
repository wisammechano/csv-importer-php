# CSV Importer

A command-line tool for importing CSV files into different database backends.

## Features

- Import CSV files into SQLite/PostgreSQL or MongoDB
- Data validation and sanitization
- Bulk import with chunking
- Error logging and reporting
- Support for multiple database backends
- Extensible for new data types

## Requirements

- PHP 8+
- PHP Extensions: mongodb, pdo_pgsql, zip
- Composer
- PostgreSQL or MongoDB
- Docker [Optional]

## Installation

1. Unzip the project or clone it from GitHub and navigate to the project directory:
```bash
cd csv-importer
```

2. Install & enable MongoDB PHP Extension
```bash
pecl install mongodb
```
Then edit php.ini to add the following line
```ini
extension=mongodb.so
```

3. Install dependencies
```bash
composer install
```

4. Configure the database connection and other config in the .env file
```bash
# One of postgres, mongodb, sqlite
# More drives can be configured as needed in config/database.php file
DB_IMPORT_DRIVER=sqlite

# Postgres
POSTGRES_DB=postgres
POSTGRES_USER=postgres
POSTGRES_PASSWORD=SUPERPASSWORDDD
POSTGRES_HOST=localhost
POSTGRES_PORT=5432


# MongoDB
MONGO_URI="mongodb://root:SUPERPASSWORDDD@mongo:27017/mongo?authSource=admin"
MONGO_INITDB_ROOT_USERNAME=root
MONGO_INITDB_ROOT_PASSWORD=SUPERPASSWORDDD
MONGO_DB=mongo
MONGO_HOST=localhost
MONGO_PORT=27017

#SQLite
SQLITE_FOREIGN_KEYS=1
SQLITE_DATABASE=./database/database.sqlite


# Logging
LOG_CHANNEL=stack
LOG_STACK=single,stderr
LOG_LEVEL_STDERR=warning
LOG_LEVEL_SINGLE=debug
```

5. Run the migrations
```bash
php csv-importer migrate
```

## Usage

```bash
php csv-importer import:csv feed.csv --table=products
```
## Supported Schemas
- `products` - Product data import
  - `gtin` (string) - Global Trade Item Number
  - `title` (string) - Product title
  - `language` (string, max:5) - Language code
  - `price` (numeric, min:0) - Product price
  - `description` (string) - Product description
  - `picture` (url) - Product image URL
  - `stock` (integer, min:0) - Stock quantity

## Extensibility

The app is built following best practices with principles like Single Responsibility, Open/Closed, and Dependency Inversion. You can extend the schemas as follows:

1. Create a new model in the `App\Models` namespace. Example: `App\Models\Product`
2. Create the model repository in the `App\Repositories` namespace. Example: `App\Repositories\ProductRepository`
3. Define the validation schema in the `App\Validators` namespace. Example: `App\Validators\ProductValidator`
4. Bind the model service in the `App\Services` namespace. Example: `App\Services\ProductService`
5. In `App\Factories\ImportServiceFactory`, bind the new model table name to the new model service. Example: `users => app(UserService::class)`
6. In `App\Commands\ImportCsvCommand`, add the new model table name to the `$tables` array. Example: `'users'`

## Supporting MongoDB

First you need to install mongodb extension for php:

```bash
pecl install mongodb
```

Then enable the extension by adding `extension=mongodb.so` to `php.ini`.

MongoDD PHP Driver is already included in the dependencies, however, model defintions need to extend MongoDB models instead of Eloquent Models. 

Simply edit `App\Models\BaseModel` to extend `MongoDB\Laravel\Eloquent\Model` instead.


## Testing
Run all tests:
```bash
php csv-importer test
```

## Logging

Logs are stored in `storage/logs/app.log`

View recent logs:

```bash
tail -f storage/logs/app.log
```

## Usage with Docker
The project is bundled with a Dockerfile that has all the required tools to run the app.

It is also bundled with a docker-compose file that has a postgres and mongo instance to test the app.

To run the app with docker:

1. Build the php image
```bash
docker build -t php-local .
```

2. Run the dependencies
```bash
docker-compose up -d
```
3. Run the needed commands

For composer:
```bash
docker run --rm --interactive --tty --network csv-importer_default --volume $PWD:/app php-local composer [command]
```

For php:
```bash
docker run --rm --interactive --tty --network csv-importer_default --volume $PWD:/app php-local php [command]
```
Example:
```bash
docker run --rm --interactive --tty --network csv-importer_default --volume $PWD:/app php-local php csv-importer import:csv feed.csv --table=products
```

As we are running the commands on the docker-compose networks services are available on these hosts:

- Postgres: `postgres`
- MongoDB: `mongo`
