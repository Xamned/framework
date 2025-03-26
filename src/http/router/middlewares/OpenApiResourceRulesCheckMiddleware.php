<?php

namespace xamned\framework\http\router\middlewares;

use League\OpenAPIValidation\PSR7\Exception\ValidationFailed;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use xamned\framework\contracts\http\router\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use xamned\framework\http\exceptions\HttpBadRequestException;

class OpenApiResourceRulesCheckMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly string $yamlFile,
    ) {
    }

    /**
     * @inheritDoc
     * @throws HttpBadRequestException
     */
    public function process(ServerRequestInterface $request, ResponseInterface $response, callable $next): void
    {
        $validator = (new ValidatorBuilder)->fromYamlFile($this->yamlFile)->getServerRequestValidator();

        try {
            $validator->validate($request);
        } catch (ValidationFailed $e) {
            throw new HttpBadRequestException('Запрос не прошел валидацию по OpenApi спецификации.', previous: $e);
        }

        $next($request, $response);
    }
}