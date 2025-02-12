<?php

namespace xamned\framework\http\exceptions;

class HttpUnauthorizedException extends HttpException
{
    protected $message = 'Нет доступа';
    protected $code = 401;
}
