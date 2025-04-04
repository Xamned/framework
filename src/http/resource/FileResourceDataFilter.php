<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\FileQueryBuilderInterface;
use xamned\framework\contracts\resource\file\FileResourceDataFilterInterface;

class FileResourceDataFilter extends BaseResourceDataFilter implements FileResourceDataFilterInterface
{
    public function __construct(
        DataBaseConnectionInterface $dbConnection,
        FileQueryBuilderInterface $queryBuilder,
    ) {
        $this->setDbConnection($dbConnection);
        $this->setQueryBuilder($queryBuilder);
    }
}