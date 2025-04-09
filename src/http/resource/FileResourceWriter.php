<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\resource\file\FileResourceWriterInterface;

class FileResourceWriter extends BaseResourceWriter implements FileResourceWriterInterface
{
    public function __construct(
        DataBaseConnectionInterface $dbConnection,
    ) {
        $this->setDbConnection($dbConnection);
    }
}