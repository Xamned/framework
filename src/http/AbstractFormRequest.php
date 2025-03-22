<?php

namespace xamned\framework\http;

use xamned\framework\contracts\http\FormRequestInterface;
use xamned\framework\contracts\validator\TypeCastServiceInterface;
use xamned\framework\contracts\validator\ValidatorFactoryInterface;
use xamned\framework\validator\exceptions\ValidationException;

abstract class AbstractFormRequest implements FormRequestInterface
{
    protected bool $skipEmptyValues = false;
    protected array $dynamicRules;
    protected array $errors;

    public function __construct(
        private readonly ValidatorFactoryInterface $validatorFactory,
        private readonly TypeCastServiceInterface $typeCastService,
    ) {
    }
    
    /**
     * Возврат правил валидации формы
     * 
     * @return array
     * Пример:
     * [
     *     [['name'], 'required'],
     *     [['name'], 'string'],
     * ]
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Динамическая установка правил валидации
     * 
     * @param array $attributes
     * @param array|string $rule
     * @return void
     * Пример:
     * $form->addRule(['name'], 'required');
     */
    public function addRule(array $attributes, array|string $rule): void
    {
        if (is_string($rule) === true) {
            $this->dynamicRules[] = [$attributes, $rule];
            return;
        }

        foreach ($rule as $oneRule) {
            $this->dynamicRules[] = [$attributes, $oneRule];
        }
    }

    /**
     * @throws ValidationException
     */
    public function validate(): void
    {
        $rules = array_merge($this->rules(), $this->dynamicRules);

        foreach ($rules as [$attributes, $rule]) {
            foreach ($attributes as $attribute) {
                $this->validateAttribute($rule, $attribute);
            }
        }
    }

    public function validateAttribute(string $rule, string $attribute): void
    {
        $validator = $this->validatorFactory->create([$rule]);

        $validator->validate($this->$attribute);

        if ($validator->hasErrors() === true) {
            $this->addError($attribute, $validator->getErrorsMessage());
            return;
        }

        $this->$attribute = $this->typeCastService->cast($this->$attribute, $validator->getPassedRules()[0]);
    }

    public function addError(string $attribute, string $message): void
    {
        $this->errors[] = "Значение \"$attribute\" не является $message.";
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setSkipEmptyValues(): void
    {
        $this->skipEmptyValues = true;
    }

    /**
     * Возврат значений формы
     * 
     * @return array
     * Пример:
     * [
     *     "id" => 1,
     *     "order_id" => 3,
     *     "name" => "Некоторое имя 1"
     * ]
     */
    public function getValues(): array
    {
        $values = [];

        foreach ($this->getAttributes() as $attribute) {
            $values[$attribute] = $this->$attribute;
        }

        return $values;
    }

    protected function getAttributes(): array
    {
        $reflection = new \ReflectionClass($this);
        $attributes = [];
        
        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic() === false) {
                $attributes[] = $property->getName();
            }
        }

        return $attributes;
    }
}
