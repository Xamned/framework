<?php

namespace xamned\framework\http\resource\enums;

enum ResourceEvent: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case PATCHED = 'patched';
    case DELETED = 'deleted';

    public function eventName(string $resourceName): string
    {
        return "xamned.{$this->value}.resource.{$resourceName}";
    }
}
