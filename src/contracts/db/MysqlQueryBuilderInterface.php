<?php

namespace xamned\framework\contracts\db;

use xamned\framework\db\mysql\StatementParameters;

interface MysqlQueryBuilderInterface extends QueryBuilderInterface
{
    function getStatement(): StatementParameters;
}
