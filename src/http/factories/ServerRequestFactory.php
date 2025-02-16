<?php

namespace xamned\framework\http\factories;

use xamned\framework\http\ServerRequest;
use xamned\framework\contracts\container\ContainerInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;

final readonly class ServerRequestFactory implements ServerRequestFactoryInterface
{
    public function __construct(
        private ContainerInterface           $container,
        private UriFactoryInterface          $uriFactory,
        private StreamFactoryInterface       $streamFactory,
        private UploadedFileFactoryInterface $fileFactory,
    ) {
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface 
    {
        if (empty($_POST) === false) {
            /** @var StreamInterface */
            $body = $this->streamFactory->createStreamFromResource(fopen('php://input', 'r'));
            $parsedBody = $this->getParsedBody($serverParams, $body);
        }

        return new ServerRequest(
            explode('/', $serverParams['SERVER_PROTOCOL'])[1] ?? '',
            $this->getHeaders($serverParams),
            $body ?? $this->container->get(StreamInterface::class),
            $this->uriFactory->createUri($uri),
            $this->getRequestTarget($serverParams),
            $method,
            $serverParams,
            $this->getCookieParams($serverParams),
            $this->getQueryParams($serverParams),
            $this->getUploadedFiles($serverParams),
            $parsedBody ?? [],
            [],
        );
    }

    private function getHeaders(array $serverParams)
    {
        $headers = [];

        foreach ($serverParams as $name => $value) {
            $matches = [];
            
            if ($name === 'CONTENT_TYPE') {
                $value = explode(', ', $value);
                $headers['Content-Type'] = $value;
                continue;
            }

            if ($name === 'CONTENT_LENGTH') {
                $value = explode(', ', $value);
                $headers['Content-Length'] = $value;
                continue;
            }

            if (preg_match('/^HTTP_(.+)/', $name, $matches) !== 1) {
                continue;
            }
            $value = explode(', ', $value);

            $name = str_replace('_', '-', mb_convert_case($matches[1],  MB_CASE_TITLE));

            $headers[$name] = $value;
        }

        return $headers;
    }

    private function getRequestTarget(array $serverParams): string
    {
        $scheme = $serverParams['REQUEST_SCHEME'] ?? '';
        $host = $serverParams['HTTP_HOST'] ?? '';
        $uri = $serverParams['REQUEST_URI'] ?? '';

        if (in_array('', [$scheme, $host, $uri]) === true) {
            return '';
        }

        return "$scheme://$host/$uri";
    }

    private function getQueryParams(array $serverParams): array
    {
        $queryString = $serverParams['QUERY_STRING'] ?? '';

        parse_str($queryString, $queryParams);
        return $queryParams;
    }

    private function getCookieParams(array $serverParams): array
    {
        $cookieParams = [];
        $cookieString = $serverParams['HTTP_COOKIE'] ?? '';

        foreach (explode('; ', $cookieString) as $cookie) {
            $pair =  explode('=', $cookie);
            $cookieParams[$pair[0]] = $pair[1] ?? '';
        }

        return $cookieParams;
    }

    private function getUploadedFiles(array $serverParams): array
    {
        if ($_SERVER !== $serverParams || $_FILES === []) {
            return [];
        }

        $uploadedFiles = [];

        foreach ($_FILES as $file => $data) {
            if (is_array($data['name']) === false) {
                $uploadedFiles[$file][$data['name']] = $this->createUploadedFileFromData($data);
                continue;
            }

            $normalizedData = $this->normalizeFilesData($data);

            foreach ($normalizedData as $element) {
                $uploadedFiles[$file][$element['name']] = $this->createUploadedFileFromData($element);
            }
        }

        return $uploadedFiles;
    }

    private function normalizeFilesData(array $data): array
    {
        $normalizedData = [];

        $firstKey = key($data);

        for ($i = 0; $i < count($data[$firstKey]); $i++) {
            $element = [];

            foreach (array_keys($data) as $key) {
                $element[$key] = $data[$key];
            }

            $normalizedData[] = $element;
        }

        return $normalizedData;
    }

    private function createUploadedFileFromData(array $data): UploadedFileInterface
    {
        return $this->fileFactory->createUploadedFile(
            $this->streamFactory->createStreamFromFile($data['tmp_name']),
            $data['size'],
            $data['error'] ?? \UPLOAD_ERR_OK,
            $data['name'],
            $data['type']
        );
    }

    private function getParsedBody(array $serverParams, StreamInterface $body): array
    {
        if ($_SERVER !== $serverParams) {
            return [];
        }

        if (preg_match('/(application\/x-www-form-urlencoded)||(multipart\/form-data)/', $serverParams['CONTENT_TYPE']) === 1) {
            return $_POST;
        }

        $body->rewind();
        $content = $body->getContents();

        $parsedBody = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $parsedBody;
        }

        return [];
    }
}
