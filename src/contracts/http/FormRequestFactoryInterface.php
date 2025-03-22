<?php

namespace xamned\framework\contracts\http;

interface FormRequestFactoryInterface
{
    function create(string $formClassName): FormRequestInterface;
}
