<?php

namespace framework\http\view;

use Exception;
use Throwable;

class ViewNotFoundException extends Exception
{
    protected $message = 'Вид не найден';

    protected $code = 404;
}
