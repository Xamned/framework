<?php

namespace xamned\framework\contracts\view;

interface ViewRendererInterface
{
    function render(string $view, array $data = []): string;
}
