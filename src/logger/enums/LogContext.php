<?php

namespace xamned\framework\logger\enums;

use xamned\framework\logger\observers\AttachLogContextObserver;
use xamned\framework\logger\observers\CleanseLogContextObserver;
use xamned\framework\logger\observers\DetachLogContextObserver;

enum LogContext: string
{
    case ATTACH = 'log.context.attach';
    case DETACH = 'log.context.detach';
    case CLEANSE = 'log.context.cleanse';
    
    public function observerClass(): string
    {
        return match ($this) {
            self::ATTACH => AttachLogContextObserver::class,
            self::DETACH => DetachLogContextObserver::class,
            self::CLEANSE => CleanseLogContextObserver::class,
        };
    }
}