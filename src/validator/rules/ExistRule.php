<?php

namespace xamned\framework\validator\rules;

use xamned\framework\contracts\container\ContainerInterface;
use xamned\framework\contracts\db\DataBaseConnectionInterface;
use xamned\framework\contracts\db\QueryBuilderInterface;
use xamned\framework\contracts\validator\ValidationRuleInterface;
use xamned\framework\validator\exceptions\ValidationException;

final class ExistRule implements ValidationRuleInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly DataBaseConnectionInterface $db,
        public string $table,
        public string $column = 'id',
        public string $caseLine = 'должно cуществовать в %s.%s'
    ) {}

    public function getName(): string
    {
        return 'exist';
    }

    public function execute(mixed $value): void
    {
        if (empty($value) === true) {
            return;
        }

        $query = $this->container->get(QueryBuilderInterface::class)
            ->from($this->table)
            ->select([$this->column => $this->column])
            ->where([$this->column => $value]);

        if ($this->db->selectOne($query) === null) {
            throw new ValidationException(sprintf($this->caseLine, $this->table, $this->column));
        }
    }
}
