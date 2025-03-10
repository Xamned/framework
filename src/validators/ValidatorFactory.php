<?php

namespace xamned\framework\validators;

use xamned\framework\contracts\validators\ValidatorFactoryInterface;
use xamned\framework\contracts\validators\ValidatorInterface;
use xamned\framework\validators\exceptions\ValidatorNotFoundException;

class ValidatorFactory implements ValidatorFactoryInterface
{
    protected array $validators = [
        'integer' => IntegerValidator::class,
        'float' => FloatValidator::class,
        'string' => StringValidator::class,
        'boolean' => BooleanValidator::class,
    ];

    public function __construct(array $validators = []) 
    {
        if ($validators !== []) {
            $this->validators = $validators;
        }
    }

    public function create(string $name, array $config = []): ValidatorInterface
    {
        if (isset($this->validators[$name]) === false) {
            throw new ValidatorNotFoundException('Валидатор не найден');
        }

        $validator = $this->validators[$name];

        if (is_array($validator) === false) {
            return new $validator($config);
        }

        $validatorClass = $validator['className'];
        unset($validator['className']);

        return new $validatorClass(array_merge($validator, $config));
    }

    public function registerValidator(string $name, array|string $validatorConfig): void
    {
        $className = is_array($validatorConfig) === true 
            ? $validatorConfig['className'] 
            : $validatorConfig;

        if (is_subclass_of( $className, ValidatorInterface::class) === false) {
            throw new \Error("$className не соответствует интерфейсу - " . ValidatorInterface::class);
        }

        $this->validators[$name] = $validatorConfig;
    }

    public function unregisterValidator($name): void
    {
        unset($this->validators[$name]);
    }

    public function registerValidators(array $validators): void
    {
        foreach ($validators as $name => $validatorConfig) {
            $this->registerValidator($name, $validatorConfig);
        }
    }

    public function unregisterValidators(array $validators): void
    {
        foreach ($validators as $name) {
            $this->unregisterValidator($name);
        }
    }
}
