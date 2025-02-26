<?php

namespace xamned\framework\http\exceptions;

class HttpBadRequestException extends HttpException
{
    protected $message = 'Некорректный запрос';
    protected $code = 400;
}