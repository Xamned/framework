<?php

namespace xamned\framework\contracts\http;

use xamned\framework\contracts\form\FormRequestInterface;

interface FormRequestFactoryInterface
{
    function create(string $formClassName, array $rules = []): FormRequestInterface;
}
