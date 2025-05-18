<?php

namespace xamned\framework\error_handler;

enum MessageTypeEnum: string
{
    case HTML = 'html';
    case JSON = 'json';
    case XML = 'xml';
    case CONSOLE = 'console';
}
