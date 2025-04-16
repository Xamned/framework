<?php

namespace xamned\framework\http\resource\responses;

use xamned\framework\contracts\http\resource\CrudResultInterface;

class DeleteResponse implements CrudResultInterface
{
    public function getStatusCode(): int
    {
        return 204;
    }

    public function getData(): mixed
    {
        return null;
    }
}
