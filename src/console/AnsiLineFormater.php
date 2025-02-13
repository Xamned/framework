<?php

namespace xamned\framework\console;

class AnsiLineFormater
{    
    /**
     * Создать строку вывода в формате ANSI
     * 
     * @param  string $message сообщение вывода
     * @param  array $format формат вывода (цвет, стиль)
     * @return string
     */
    public function format(string $message, array $format = []): string
    {
        $code = implode(';', $format);

        return "\033[0m" . ($code !== '' ? "\033[" . $code . 'm' : '') . $message . "\033[0m";
    }
}
