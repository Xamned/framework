<?php

namespace xamned\framework\validator;

use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\contracts\validator\ValidatorFactoryInterface;
use xamned\framework\contracts\validator\ValidatorInterface;
use xamned\framework\validator\rules\BooleanRule;
use xamned\framework\validator\rules\FloatRule;
use xamned\framework\validator\rules\IntegerRule;
use xamned\framework\validator\rules\RequireRule;
use xamned\framework\validator\rules\StringRule;
use xamned\framework\validator\exceptions\ValidationRuleNotFoundException;

class ValidatorFactory implements ValidatorFactoryInterface
{
    protected array $rules = [
        'integer' => IntegerRule::class,
        'float' => FloatRule::class,
        'string' => StringRule::class,
        'boolean' => BooleanRule::class,
        'require' => RequireRule::class,
    ];

    protected string $validator = Validator::class;

    public function __construct(
        private readonly ContainerInterface $container,
        array $rules = []
    ) {
        $this->rules = array_merge($this->rules, $rules);
    }

    public function create(array $ruleNames): ValidatorInterface
    {
        $rules = [];

        foreach ($ruleNames as $ruleName) {
            if (isset($this->rules[$ruleName]) === false) {
                throw new ValidationRuleNotFoundException('Правило валидации не найдено');
            }

            $rules[] = $this->createRule($ruleName);
        }

        return $this->container->build($this->validator, ['rules' => $rules]);
    }

    private function createRule(string $name): ValidationRuleInterface
    {
        $ruleConfig = $this->rules[$name];

        if (is_array($ruleConfig) === false) {
            return $this->container->build($ruleConfig);
        }

        $ruleClass = $ruleConfig['className'];
        unset($ruleConfig['className']);

        return $this->container->build($ruleClass, $ruleConfig);
    }

    public function attachRule(string $name, array|string $ruleConfig): void
    {
        $className = is_array($ruleConfig) === true 
            ? $ruleConfig['className'] 
            : $ruleConfig;

        if (is_subclass_of($className, ValidationRuleInterface::class) === false) {
            throw new \InvalidArgumentException("$className не соответствует интерфейсу - " . ValidationRuleInterface::class);
        }

        $this->rules[$name] = $ruleConfig;
    }

    public function detachRule(string $name): void
    {
        unset($this->rules[$name]);
    }

    public function attachRules(array $rules): void
    {
        foreach ($rules as $name => $ruleConfig) {
            $this->attachRule($name, $ruleConfig);
        }
    }

    public function detachRules(array $rules): void
    {
        foreach ($rules as $name) {
            $this->detachRule($name);
        }
    }

    public function setValidator(string $validator): void
    {
        if (is_subclass_of($validator, ValidatorInterface::class) === false) {
            throw new \InvalidArgumentException("$validator не соответствует интерфейсу - " . ValidatorInterface::class);
        }

        $this->validator = $validator;
    }
}
