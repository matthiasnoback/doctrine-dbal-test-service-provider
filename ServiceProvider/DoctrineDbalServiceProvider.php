<?php

namespace Noback\PHPUnitTestServiceContainer\ServiceProvider;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Noback\PHPUnitTestServiceContainer\ServiceContainerInterface;
use Noback\PHPUnitTestServiceContainer\ServiceProviderInterface;

class DoctrineDbalServiceProvider implements ServiceProviderInterface
{
    private $schema;

    public function __construct(Schema $schema = null)
    {
        if ($schema === null) {
            $schema = new Schema();
        }

        $this->schema = $schema;
    }

    public function register(ServiceContainerInterface $serviceContainer)
    {
        $serviceContainer['doctrine_dbal.connection_configuration'] = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        $serviceContainer['doctrine_dbal.event_manager'] = $serviceContainer->share(
            function () {
                return new EventManager();
            }
        );

        $serviceContainer['doctrine_dbal.connection'] = $serviceContainer->share(
            function (ServiceContainerInterface $serviceContainer) {
                return DriverManager::getConnection(
                    $serviceContainer['doctrine_dbal.connection_configuration'],
                    null,
                    $serviceContainer['doctrine_dbal.event_manager']
                );
            }
        );

        $serviceContainer['doctrine_dbal.schema'] = $this->schema;
    }

    public function setUp(ServiceContainerInterface $serviceContainer)
    {
        $this->createSchema($serviceContainer['doctrine_dbal.connection'], $serviceContainer['doctrine_dbal.schema']);
    }

    public function tearDown(ServiceContainerInterface $serviceContainer)
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
