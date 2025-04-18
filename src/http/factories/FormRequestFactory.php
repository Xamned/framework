<?php

namespace xamned\framework\http\factories;

use Psr\Http\Message\ServerRequestInterface;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\form\FormRequestInterface;
use xamned\framework\contracts\http\FormRequestFactoryInterface;
use xamned\framework\form\FormRequest;

class FormRequestFactory implements FormRequestFactoryInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ServerRequestInterface $request,
    ) {}

    public function create(string $formClassName, array $rules = []): FormRequestInterface
    {
        if (is_subclass_of($formClassName, FormRequestInterface::class) === false) {
            throw new \InvalidArgumentException("$formClassName не соответствует интерфейсу - " . FormRequestInterface::class);
        }

        /** @var FormRequestInterface */
        $form = $this->container->get($formClassName);

        foreach ($rules as $rule) {
            $form->addRule(...$rule);

            if ($form instanceof FormRequest) {
                $this->addAttributesToForm($form, $rule[0]);
            }
        }

        $form->load($this->request->getParsedBody());

        return $form;
    }

    protected function addAttributesToForm(FormRequest $form, array $attributes): void
    {
        foreach ($attributes as $attribute) {
            if ($form->hasAttribute($attribute) === false) {
                $form->addAttribute($attribute);
            }
        }
    }
}
