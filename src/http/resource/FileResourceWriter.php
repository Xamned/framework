<?php

namespace xamned\framework\http\resource;

use xamned\framework\contracts\db\FileConnectionInterface;
use xamned\framework\contracts\resource\file\FileResourceWriterInterface;

class FileResourceWriter extends BaseResourceWriter implements FileResourceWriterInterface
{
    public function __construct(
        FileConnectionInterface $dbConnection,
    ) {
        $this->setDbConnection($dbConnection);
    }
}