<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\resource\mysql\DbResourceWriterInterface;

class MysqlResourceWriter extends BaseResourceWriter implements DbResourceWriterInterface
{
    public function __construct(
        DataBaseConnectionInterface $dbConnection,
    ) {
        $this->setDbConnection($dbConnection);
    }
}