<?php

namespace framework\contracts\logger;

interface DebugTagGeneratorInterface
{
    public function generateDebugTag(string $name, ?string $tag = null): void;
}