<?php

namespace xamned\framework\http;

enum MessageTypeEnum: string
{
    case HTML = 'html';

    case JSON = 'json';

    case XML = 'xml';
}
