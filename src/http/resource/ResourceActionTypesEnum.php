<?php

namespace xamned\framework\http\resource;

enum ResourceActionTypesEnum: string
{
    case CREATE = 'create';
    case PATCH  = 'patch';
    case UPDATE = 'update';
}
