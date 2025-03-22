<?php

namespace xamned\framework\contracts\db;

interface ConnectionFactoryInterface
{
    function createConnection(array $config): DataBaseConnectionInterface;
}
