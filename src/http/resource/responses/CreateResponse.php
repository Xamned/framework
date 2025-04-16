<?php

namespace xamned\framework\http\resource\responses;

use xamned\framework\contracts\http\resource\CrudResultInterface;

class CreateResponse implements CrudResultInterface
{
    public function getStatusCode(): int
    {
        return 201;
    }

    public function getData(): mixed
    {
        return null;
    }
}
