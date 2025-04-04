<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\MysqlQueryBuilderInterface;

class MysqlResourceDataFilter extends BaseResourceDataFilter
{
    public function __construct(
        DataBaseConnectionInterface $dbConnection,
        MysqlQueryBuilderInterface $queryBuilder,
    ) {
        $this->setDbConnection($dbConnection);
        $this->setQueryBuilder($queryBuilder);
    }
}