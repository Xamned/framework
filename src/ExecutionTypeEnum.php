<?php

namespace xamned\framework;

enum ExecutionTypeEnum: string
{
    case PRODUCTION = 'prod';

    case DEVELOPMENT = 'dev';

    case TEST = 'test';
}
