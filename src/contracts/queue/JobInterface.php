<?php

namespace xamned\framework\contracts\queue;

interface JobInterface
{
    public function run();
}