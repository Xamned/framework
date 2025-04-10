<?php

namespace xamned\framework\contracts\db;

use xamned\framework\db\file\enums\FileTypeEnum;

interface ListBuilderFactoryInterface
{
    function create(FileTypeEnum $fileType, string $fileName, array $resourceColumns = []): ListBuilderInterface;
}
