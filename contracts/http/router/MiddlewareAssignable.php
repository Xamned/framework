<?php

namespace framework\contracts\http\router;

interface MiddlewareAssignable
{
    /**
     * Добавление мидлвеера
     * 
     * @param  callable|string $middleware коллбек функция или неймспейс класса мидлвеера
     * @return void
     */
    function addMiddleware(callable|string $middleware): void;
}
