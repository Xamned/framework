<?php

namespace xamned\framework\http\resource;

use Psr\Http\Message\ServerRequestInterface;
use xamned\framework\contracts\http\resource\ResourceWriterInterface;
use xamned\framework\contracts\resource\RelationshipsManagerInterface;

class RelationshipsManager implements RelationshipsManagerInterface
{
    protected string $resource;
    protected array $relationships;

    public function __construct(
        protected ResourceWriterInterface $resourceWriter,
    ) {}

    public function setResourceName(string $name): static
    {
        $this->resource = $name;

        return $this;
    }

    public function setRelationships(array $relationships): static
    {
        $this->relationships = $relationships;

        return $this;
    }

    public function manage(ServerRequestInterface $request, array $resourceItem): void
    {
        $method = $this->getWriterMethod($request);
        
        if (method_exists($this->resourceWriter, $method) === false) {
            return;
        }

        $relationships = $request->getParsedBody()['relationships'];

        foreach ($this->relationships as $name => $config) {
            if (isset($relationships[$name], $config[$method]) === false) {
                continue;
            }

            $managableData = $relationships[$name]['data'];

            $this->resourceWriter->setResourceName($config['table']);

            foreach ($managableData as $item) {
                $params = $config[$method]($resourceItem, $item);
                
                $this->resourceWriter->$method(...$params);
            }
        }
    }

    private function getWriterMethod(ServerRequestInterface $request): string
    {
        $method = strtolower($request->getMethod());
        return match ($method) {
            'post' => 'create',
            'put' => 'update',
            'patch' => 'patch',
            'delete' => 'delete',
            default => '',
        };
    }
}