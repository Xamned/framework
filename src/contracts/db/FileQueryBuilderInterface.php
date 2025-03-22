<?php

namespace xamned\framework\contracts\db;

use xamned\framework\db\file\StatementParameters;

interface FileQueryBuilderInterface extends QueryBuilderInterface
{
    function getStatement(): StatementParameters;
}

