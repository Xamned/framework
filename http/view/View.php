<?php

namespace framework\http\view;

use framework\contracts\view\ViewRendererInterface;
use Exception;
use Throwable;

final class View implements ViewRendererInterface
{
    private string $path = '';

    public function __construct(
        private readonly array $defaultPaths = [],
    )  {
    }

    public function render(string $view, array $data = []): string
    {
        ob_start();
        extract($data);

        try {
            if (empty($this->path) === true) {
                require $view . '.php';
            }
            if (empty($this->path) === false) {
                require $this->path . $view . ".php";
            }
        } catch (Throwable $e) {
            throw new ViewNotFoundException();
        }

        return ob_get_clean();
    }

    public function setDefaultPath(string $alias): void
    {
        if (isset($this->defaultPaths[$alias]) === false) {
            throw new Exception("Модуль $alias не определён");
        }

        if (is_dir($this->defaultPaths[$alias]) === false) {
            throw new Exception("Модуль $alias не существует");
        }

        $this->path = $this->defaultPaths[$alias] . '/';
    }
}
