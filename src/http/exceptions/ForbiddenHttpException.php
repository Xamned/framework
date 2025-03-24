<?php

namespace xamned\framework\http\exceptions;

class ForbiddenHttpException extends HttpException
{
    protected $message = 'Доступ запрещен';
    protected $code = 403;
}