<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\FileQueryBuilderInterface;

class MysqlResourceDataFilter extends BaseResourceDataFilter
{
    public function __construct(
        DataBaseConnectionInterface $dbConnection,
        FileQueryBuilderInterface $queryBuilder,
    ) {
        $this->setDbConnection($dbConnection);
        $this->setQueryBuilder($queryBuilder);
    }
}