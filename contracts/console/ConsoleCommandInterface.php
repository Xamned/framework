<?php

namespace framework\contracts\console;

interface ConsoleCommandInterface
{
    public static function getDescription(): string;
    
    public static function getSignature(): string;

    /**
     * Выполнить команду
     * 
     * @return void
     */
    public function execute(): void;
}