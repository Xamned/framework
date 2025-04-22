<?php

namespace xamned\framework\contracts\resource;

use Psr\Http\Message\ServerRequestInterface;

interface RelationshipsManagerInterface
{
    function setResourceName(string $name): static;

    function setRelationships(array $relationships): static;

    function manage(ServerRequestInterface $request, array $resourceItem): void;
}