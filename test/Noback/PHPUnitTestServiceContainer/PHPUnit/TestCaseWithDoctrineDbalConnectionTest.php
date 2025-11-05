<?php

namespace Noback\PHPUnitTestServiceContainer\PHPUnit;

use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

final class TestCaseWithDoctrineDbalConnectionTest extends TestCase
{
    use TestCaseWithDoctrineDbalConnection;

    protected function createSchema(): Schema
    {
        $schema = new Schema();

        $table = $schema->createTable('some_table');

        $table->addColumn('some_column', 'string');

        return $schema;
    }

    public function test_a_connection_is_available_and_the_schema_has_been_created()
    {
        $value = 'some value';

        $this->getConnection()->insert('some_table', array('some_column' => $value));

        $row = $this->getConnection()->executeQuery('SELECT * FROM some_table')->fetchAssociative();

        $this->assertSame($value, $row['some_column']);
    }

    public function test_in_between_tests_the_database_will_be_recreated()
    {
        $result = $this->getConnection()->executeQuery('SELECT COUNT(*) AS row_count FROM some_table')->fetchAssociative();

        $this->assertEquals(0, $result['row_count']);
    }
}
