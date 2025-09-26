<?php

namespace xamned\framework\form;

use xamned\framework\contracts\form\FormRequestInterface;
use xamned\framework\contracts\validator\TypeCastTranslatorInterface;
use xamned\framework\contracts\validator\ValidatorFactoryInterface;
use xamned\framework\validator\exceptions\ValidationException;
use xamned\framework\validator\rules\UniqueRule;

abstract class AbstractFormRequest implements FormRequestInterface
{
    protected bool $skipEmptyValues = false;
    protected array $dynamicRules = [];
    protected array $errors = [];

    public function __construct(
        private readonly ValidatorFactoryInterface $validatorFactory,
        private readonly TypeCastTranslatorInterface $typeCastService,
    ) {}

    /**
     * Возвращает список правил, в которые атрибуты передаются все сразу, а не по одному
     *
     * @return array
     */
    private function getRulesForPassingAllAttributes(): array
    {
        return [
            'unique' => UniqueRule::class,
        ];
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
        $this->dynamicRules[] = [$attributes, $rule];
    }

    /**
     * @throws ValidationException
     */
    public function validate(): void
    {
        $rules = array_merge($this->rules(), $this->dynamicRules);

        foreach ($rules as [$attributes, $rule]) {
            $key = is_array($rule) === true ? $rule[0] : $rule;

            if (array_key_exists($key, $this->getRulesForPassingAllAttributes()) === true) {
                $this->validateAttribute($rule, $attributes);

                continue;
            }

            foreach ($attributes as $attribute) {
                $this->validateAttribute($rule, $attribute);
            }
        }
    }

    public function validateAttribute(array|string $rule, array|string $attribute): void
    {
        if ($this->skipEmptyValues === true && empty($this->$attribute) === true) {
            return;
        }
        
        $validator = $this->validatorFactory->create([$rule]);

        if (is_array($attribute) === true) {
            $attributeValues = $this->getAttributesValueByArray($attribute);

            $validator->validate($attributeValues);
        }

        if (is_array($attribute) === false) {
            $validator->validate($this->$attribute);
        }

        if ($validator->hasErrors() === true) {
            $this->addError($attribute, $validator->getErrorsCasesLine());
            return;
        }

        if (is_array($attribute) === false) {
            $this->$attribute = $this->typeCastService->translate($this->$attribute, $validator->getPassedRules()[0]);
        }
    }

    public function addError(array|string $attribute, string $message): void
    {
        if (is_array($attribute) === true) {
            $this->errors[] = 'Значения ' . implode(',', $attribute) . " $message";

            return;
        }

        $this->errors[] = "Значение \"$attribute\" $message.";
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
            if ($this->skipEmptyValues === true && empty($this->$attribute) === true) {
                continue;
            }
            
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

    public function load(array $data): void
    {
        foreach($this->getAttributes() as $attribute) {
            $this->$attribute = $data[$attribute] ?? null;
        }
    }

    private function getAttributesValueByArray(array $attributes): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $result[$attribute] = $this->$attribute;
        }

        return $result;
    }
}
