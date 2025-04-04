<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;

class MysqlResourceWriter extends BaseResourceWriter
{
    public function __construct(
        DataBaseConnectionInterface $dbConnection,
    ) {
        $this->setDbConnection($dbConnection);
    }
}