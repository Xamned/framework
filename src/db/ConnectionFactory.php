<?php

namespace xamned\framework\db;

use xamned\framework\contracts\db\ConnectionFactoryInterface;
use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\db\mysql\Connection as MysqlConnection;
use xamned\framework\db\file\Connection as FileConnection;

class ConnectionFactory implements ConnectionFactoryInterface
{
    public function createConnection(array $config): DataBaseConnectionInterface
    {
        return match ($config['driver']) {
            'mysql' => new MysqlConnection($config),
            'file' => new FileConnection($config),
        };
    }
}
