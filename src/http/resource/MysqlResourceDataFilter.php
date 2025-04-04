<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\MysqlQueryBuilderInterface;
use xamned\framework\contracts\resource\mysql\DbResourceDataFilterInterface;

class MysqlResourceDataFilter extends BaseResourceDataFilter implements DbResourceDataFilterInterface
{
    public function __construct(
        DataBaseConnectionInterface $dbConnection,
        MysqlQueryBuilderInterface $queryBuilder,
    ) {
        $this->setDbConnection($dbConnection);
        $this->setQueryBuilder($queryBuilder);
    }
}