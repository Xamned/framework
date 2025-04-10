<?php

namespace xamned\framework\http\factories;

use Psr\Http\Message\ServerRequestInterface;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\form\FormRequestInterface;
use xamned\framework\contracts\http\FormRequestFactoryInterface;

class FormRequestFactory implements FormRequestFactoryInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ServerRequestInterface $request,
    ) {}

    public function create(string $formClassName): FormRequestInterface
    {
        if (is_subclass_of($formClassName, FormRequestInterface::class) === false) {
            throw new \InvalidArgumentException("$formClassName не соответствует интерфейсу - " . FormRequestInterface::class);
        }

        $form = $this->container->get($formClassName);

        $form->load($this->request->getParsedBody());

        return $form;
    }
}
