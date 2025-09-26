<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;
use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

class UniqueRule implements ValidationRuleInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly DataBaseConnectionInterface $db,
        public string $table,
        public string $caseLine = 'значение должно быть уникальным в %s'
    ) {}

    public function getName(): string
    {
        return 'unique';
    }

    public function execute(mixed $value): void
    {
        if (empty($value) === true) {
            return;
        }

        $conditions = [];

        foreach ($value as $attributeName => $attributeValue) {
            $conditions[$attributeName] = $attributeValue;
        }

        $query = $this->container->get(QueryBuilderInterface::class)
            ->from($this->table)
            ->select(['cnt' => 'COUNT(*)'])
            ->where($conditions);

        if ((bool)($this->db->selectOne($query)['cnt']) === true) {
            throw new ValidationException(sprintf($this->caseLine, $this->table));
        }
    }
}
