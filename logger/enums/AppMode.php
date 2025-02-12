<?php

namespace framework\logger\enums;

enum AppMode: string
{
    case WEB = 'web';
    case CONSOLE = 'console';
}