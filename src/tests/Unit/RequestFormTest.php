<?php

namespace unit;

use Codeception\Test\Unit;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Support\UnitTester;
use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\form\FormRequestInterface;
use xamned\framework\contracts\validator\ValidatorFactoryInterface;
use xamned\framework\form\FormRequest;
use xamned\framework\http\factories\FormRequestFactory;
use xamned\framework\validator\TypeCastTranslator;
use xamned\framework\validator\Validator;

class RequestFormTest extends Unit
{
    protected UnitTester $tester;

    public function testValidRequireRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'require'],
            ],
            ['attributes' => ['foo' => 'bar']],
        );

        $finalForm->validate();

        $this->assertEmpty($finalForm->getErrors());
    }

    public function testInvalidRequireRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'require'],
            ],
            ['attributes' => ['loo' => 'bar']],
        );

        $finalForm->validate();

        $this->assertNotEmpty($finalForm->getErrors());
    }

    public function testValidStringRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'string'],
            ],
            ['attributes' => ['foo' => 'string']],
        );

        $finalForm->validate();

        $this->assertEmpty($finalForm->getErrors());
    }

    public function testInvalidStringRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'string'],
            ],
            ['attributes' => ['foo' => 55]],
        );

        $finalForm->validate();

        $this->assertNotEmpty($finalForm->getErrors());
    }

    public function testValidBooleanRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'boolean'],
            ],
            ['attributes' => ['foo' => false]],
        );

        $finalForm->validate();

        $this->assertEmpty($finalForm->getErrors());
    }

    public function testInvalidBooleanRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'boolean'],
            ],
            ['attributes' => ['foo' => 55]],
        );

        $finalForm->validate();

        $this->assertNotEmpty($finalForm->getErrors());
    }

    public function testValidFloatRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'float'],
            ],
            ['attributes' => ['foo' => 30.15]],
        );

        $finalForm->validate();

        $this->assertEmpty($finalForm->getErrors());
    }

    public function testInvalidFloatRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'float'],
            ],
            ['attributes' => ['foo' => 'bar']],
        );

        $finalForm->validate();

        $this->assertNotEmpty($finalForm->getErrors());
    }

    public function testValidIntegerRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'integer'],
            ],
            ['attributes' => ['foo' => 30]],
        );

        $finalForm->validate();

        $this->assertEmpty($finalForm->getErrors());
    }

    public function testInvalidIntegerRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], 'integer'],
            ],
            ['attributes' => ['foo' => 'bar']],
        );

        $finalForm->validate();

        $this->assertNotEmpty($finalForm->getErrors());
    }

    public function testValidRegexRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], ['regex', 'pattern' => '/^\d+$/']],
            ],
            ['attributes' => ['foo' => '30087']],
        );

        $finalForm->validate();

        $this->assertEmpty($finalForm->getErrors());
    }

    public function testInvalidRegexRule(): void
    {
        $finalForm = $this->prepareFormWithRulesAndBody(
            [
                [['foo'], ['regex', 'pattern' => '/^\d+$/']],
            ],
            ['attributes' => ['foo' => '124aa']],
        );

        $finalForm->validate();

        $this->assertNotEmpty($finalForm->getErrors());
    }

    private function prepareFormWithRulesAndBody(
        array $rules,
        array $body,
    ): FormRequestInterface {
        $di = $this->createMock(ContainerInterface::class);
        $validatorFactory = $this->createMock(ValidatorFactoryInterface::class);
        $typeCast = new TypeCastTranslator();

        $validator = new Validator([]);

        $validatorFactory->method('create')
            ->willReturn($validator);

        $formRequest = new FormRequest(
            $validatorFactory,
            $typeCast,
        );

        $di->method('get')
            ->willReturnMap([[FormRequest::class, $formRequest],]);

        $request = $this->createMock(ServerRequestInterface::class);

        $request->method('getParsedBody')->willReturn($body);

        $formRequestFactory = new FormRequestFactory(
            $di,
            $request,
        );

        return $formRequestFactory->create(FormRequest::class, 'attributes', $rules);
    }
}
