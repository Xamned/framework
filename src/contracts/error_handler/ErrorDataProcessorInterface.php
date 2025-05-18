<?php

namespace xamned\framework\contracts\error_handler;

use Throwable;

interface ErrorDataProcessorInterface
{
    function process(Throwable $error): array;
}
