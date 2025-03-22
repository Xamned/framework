<?php

namespace xamned\framework\http;

use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\http\FormRequestFactoryInterface;
use xamned\framework\contracts\http\FormRequestInterface;

class FormRequestFactory implements FormRequestFactoryInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function create(string $formClassName): FormRequestInterface
    {
        if (is_subclass_of($formClassName, FormRequestInterface::class) === false) {
            throw new \InvalidArgumentException("$formClassName не соответствует интерфейсу - " . FormRequestInterface::class);
        }

        return $this->container->get($formClassName);
    }
}
