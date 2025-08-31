<?php

namespace xamned\framework\validator;

use app\components\validator\rules\DateRule;
use app\components\validator\rules\ExistRule;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\contracts\validator\ValidatorFactoryInterface;
use xamned\framework\contracts\validator\ValidatorInterface;
use xamned\framework\validator\rules\BooleanRule;
use xamned\framework\validator\rules\FloatRule;
use xamned\framework\validator\rules\IntegerRule;
use xamned\framework\validator\rules\RegexRule;
use xamned\framework\validator\rules\RequireRule;
use xamned\framework\validator\rules\StringRule;

class ValidatorFactory implements ValidatorFactoryInterface
{
    protected array $rules = [
        'integer' => IntegerRule::class,
        'float' => FloatRule::class,
        'string' => StringRule::class,
        'boolean' => BooleanRule::class,
        'require' => RequireRule::class,
        'regex' => RegexRule::class,
        'date' => DateRule::class,
        'exist' => ExistRule::class,
    ];

    protected string $validator = Validator::class;

    public function __construct(
        private readonly ContainerInterface $container,
        array $rules = []
    ) {
        $this->rules = array_merge($this->rules, $rules);
    }

    public function create(array $rules): ValidatorInterface
    {
        $result = [];

        foreach ($rules as $rule) {
            $ruleName = $rule;
            
            if (is_array($rule) === true) {
                $ruleConfig = $rule;

                $ruleName = array_shift($ruleConfig);
            }

            if (is_array($rule) === false) {
                $ruleConfig = [];
            }

            $result[] = $this->createRule($ruleName, $ruleConfig);
        }

        return $this->container->build($this->validator, ['rules' => $result]);
    }

    private function createRule(string $name, array $config = []): ValidationRuleInterface
    {
        if (isset($this->rules[$name]) === false) {
            $this->checkByRuleInterface($name);

            return $this->container->build($name, $config);
        }

        $ruleConfig = $this->rules[$name];

        if (is_array($ruleConfig) === false) {
            return $this->container->build($ruleConfig, $config);
        }

        $ruleClass = $ruleConfig['className'];
        unset($ruleConfig['className']);

        return $this->container->build($ruleClass, array_merge($ruleConfig, $config));
    }

    /**
     * @param string $className
     * @throws \InvalidArgumentException
     * @return void
     */
    private function checkByRuleInterface(string $className): void
    {
        $contract = ValidationRuleInterface::class;

        if (is_subclass_of($className, $contract) === false) {
            throw new \InvalidArgumentException("$className не соответствует интерфейсу - $contract");
        }
    }

    public function attachRule(string $name, array|string $ruleConfig): void
    {
        $className = is_array($ruleConfig) === true 
            ? $ruleConfig['className'] 
            : $ruleConfig;

        $this->checkByRuleInterface($className);

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
        $this->checkByRuleInterface($validator);

        $this->validator = $validator;
    }
}
