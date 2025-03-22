<?php

namespace xamned\framework\http\resource;

enum ResourceActionTypesEnum: string
{
    case INDEX = 'index';
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case PATCH  = 'patch';
    case DELETE  = 'delete';
}
