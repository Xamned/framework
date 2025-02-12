<?php

namespace xamned\framework\logger\enums;

enum LogLevel: string
{
    case CRITICAL = '1';
    case ERROR = '2';
    case WARNING = '3';
    case INFO = '4';
    case DEBUG = '5';
}