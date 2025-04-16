<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\FileConnectionInterface;
use xamned\framework\contracts\db\FileQueryBuilderInterface;
use xamned\framework\contracts\resource\file\FileResourceDataFilterInterface;

class FileResourceDataFilter extends BaseResourceDataFilter implements FileResourceDataFilterInterface
{
    public function __construct(
        FileConnectionInterface $dbConnection,
        FileQueryBuilderInterface $queryBuilder,
    ) {
        $this->setDbConnection($dbConnection);
        $this->setQueryBuilder($queryBuilder);
    }
}