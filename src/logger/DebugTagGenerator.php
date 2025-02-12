<?php

namespace xamned\framework\logger;

use xamned\framework\contracts\logger\DebugTagGeneratorInterface;
use xamned\framework\contracts\logger\DebugTagStorageInterface;
use xamned\framework\logger\enums\AppMode;

class DebugTagGenerator implements DebugTagGeneratorInterface
{
    public function __construct(
        private readonly string $mode,
        private readonly DebugTagStorageInterface $debugTagStorage,
    ) {
    }

    private function getallheaders(): array 
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
    
        return $headers;
    }
    
    public function generateDebugTag(string $name, ?string $tag = null): void
    {        
        if ($this->debugTagStorage->issetTag() === true && $this->mode === AppMode::WEB->value) {
            throw new \Exception('Запрещено обновление значения тега при использовании генератора в ядре обработки HTTP-запросов');
        }

        $headers = $this->getallheaders();

        if (isset($headers['X-Debug-Tag']) === true) {
            $this->debugTagStorage->setTag($headers['X-Debug-Tag']);
            return;
        }

        $host = gethostname();
        $time = time();
        $tag ??= uniqid();

        $key = "x-debug-tag-$name-$tag-$host-$time";

        $this->debugTagStorage->setTag(md5($key));
    }
}