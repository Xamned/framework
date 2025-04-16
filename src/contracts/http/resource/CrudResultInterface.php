<?php

namespace xamned\framework\contracts\http\resource;

interface CrudResultInterface
{
    function getStatusCode(): int;

    function getData(): mixed;
}
