<?php

namespace framework\contracts\logger;

interface LoggerInterface
{
    /**
     * Логирование критической ошибки
     * 
     * @param mixed $message сообщение
     * @return void
     */
    public function critical(mixed $message): void;

    /**
     * Логирование ошибки
     * 
     * @param mixed $message сообщение
     * @return void
     */
    public function error(mixed $message): void;

    /**
     * Логирование предупредительного сообщения
     * 
     * @param mixed $message сообщение
     * @return void
     */
    public function warning(mixed $message): void;

    /**
     * Логирование информационного сообщения
     * 
     * @param mixed $message сообщение
     * @return void
     */
    public function info(mixed $message): void;

    /**
     * Логирование сообщения отладки
     * 
     * @param mixed $message сообщение
     * @return void
     */
    public function debug(mixed $message): void;
}