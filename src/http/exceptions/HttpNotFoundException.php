<?php

namespace xamned\framework\http\exceptions;

class HttpNotFoundException extends HttpException
{
    protected $message = 'Страница не найдена';
    protected $code = 404;
}