<?php

namespace xamned\framework\error_handler\renderers;

use xamned\framework\contracts\error_handler\ErrorRendererInterface;
use xamned\framework\http\view\View;

class HtmlErrorRenderer implements ErrorRendererInterface
{
    public function __construct(
        private readonly View $view,
    ) {}

    public function render(array $data): string
    {
        return $this->view->render(__DIR__ . '/views/error', $data);
    }
}
