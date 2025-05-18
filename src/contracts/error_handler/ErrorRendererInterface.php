<?php

namespace xamned\framework\contracts\error_handler;

interface ErrorRendererInterface
{
    function render(array $data): string;
}
