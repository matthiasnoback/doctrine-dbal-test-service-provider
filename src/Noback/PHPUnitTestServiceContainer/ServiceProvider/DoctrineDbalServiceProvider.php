<?php

namespace Noback\PHPUnitTestServiceContainer\ServiceProvider;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Noback\PHPUnitTestServiceContainer\ServiceContainer;
use Noback\PHPUnitTestServiceContainer\ServiceProvider;
use Pimple\Container;

final class DoctrineDbalServiceProvider implements ServiceProvider
{
    private $schema;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function register(Container $serviceContainer)
    {
        $serviceContainer['doctrine_dbal.connection_configuration'] = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $serviceContainer['doctrine_dbal.event_manager'] = function () {
            return new EventManager();
        };

        $serviceContainer['doctrine_dbal.connection'] = function (ServiceContainer $serviceContainer) {
            return DriverManager::getConnection(
                $serviceContainer['doctrine_dbal.connection_configuration'],
                null,
                $serviceContainer['doctrine_dbal.event_manager']
            );
        };

        $serviceContainer['doctrine_dbal.schema'] = $this->schema;
    }

    public function setUp(ServiceContainer $serviceContainer)
    {
        $this->createSchema($serviceContainer['doctrine_dbal.connection'], $serviceContainer['doctrine_dbal.schema']);
    }

    public function tearDown(ServiceContainer $serviceContainer)
    {
        $this->closeConnection($serviceContainer['doctrine_dbal.connection']);
    }

    private function createSchema(Connection $connection, Schema $schema)
    {
        foreach ($schema->toSql($connection->getDatabasePlatform()) as $sql) {
            $connection->exec($sql);
        }
    }

    private function closeConnection(Connection $connection)
    {
        $connection->close();
    }
}
