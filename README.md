# Doctrine DBAL service provider for unit tests

[![CI](https://github.com/matthiasnoback/doctrine-dbal-test-service-provider/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/matthiasnoback/doctrine-dbal-test-service-provider/actions/workflows/ci.yml)

This library contains a service provider to be used with a [service container for PHPUnit
tests](https://github.com/matthiasnoback/phpunit-test-service-container).

## Usage

Use the trait `Noback\PHPUnitTestServiceContainer\PHPUnit\TestCaseWithDoctrineDbalConnection` in your test class.
You then need to implement the `createSchema()` and return an instance of `Doctrine\DBAL\Schema\Schema`.

For each test method a database connection (of instance `Doctrine\DBAL\Connection`) will be available. Also the schema
returned by `createSchema()` will be created in the database. The database itself is (by default) an SQLite in-memory
database, which will leave no traces on the filesystem.

```php
<?php

use Noback\PHPUnitTestServiceContainer\PHPUnit\TestCaseWithDoctrineDbalConnection;
use Doctrine\DBAL\Schema\Schema;

final class StorageTest
{
    use TestCaseWithDoctrineDbalConnection;
    
    /**
     * @test
     */
    public function something()
    {
        $connection = $this->getConnection();

        $connection->insert('some_table', array('some_column' => 'value'));

        ...
    }

    protected function createSchema()
    {
        $schema = new Schema();

        $table = $schema->createTable('some_table');

        $table->addColumn('some_column', 'string');

        return $schema;
    }
}
```

## Read more

- [Doctrine DBAL](http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/)
- [PHPUnit test service container](https://github.com/matthiasnoback/phpunit-test-service-container)
