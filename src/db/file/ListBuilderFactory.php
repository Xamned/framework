<?php

namespace xamned\framework\db\file;

use xamned\framework\contracts\db\ListBuilderFactoryInterface;
use xamned\framework\contracts\db\ListBuilderInterface;
use xamned\framework\db\file\enums\FileTypeEnum;

class ListBuilderFactory implements ListBuilderFactoryInterface
{
    /**
     * @param FileTypeEnum $fileType
     * @return AbstractListBuilder
     */
    public function create(FileTypeEnum $fileType, string $fileName, array $resourceColumns = []): ListBuilderInterface
    {
        return match ($fileType) {
            FileTypeEnum::JSON => new JsonListBuilder($fileName, $resourceColumns),
            default => throw new \InvalidArgumentException("Для типа файлов \"{$fileType->value}\" не существует реализации "
                . ListBuilderInterface::class),
        };
    }
}
