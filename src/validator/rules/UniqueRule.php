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
        /** @var string[] */
        public array $columns,
        public string $caseLine = 'значение должно быть уникальным в %s.%s'
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

        $values = is_array($value) ? $value : [$this->columns[0] => $value];

        $conditions = [];
        foreach ($this->columns as $column) {
            if (array_key_exists($column, $values) === false) {
                throw new ValidationException(sprintf('Не передано значение для колонки "%s"', $column));
            }
            $conditions[$column] = $values[$column];
        }

        $query = $this->container->get(QueryBuilderInterface::class)
            ->from($this->table)
            ->select(['id' => 'id'])
            ->where($conditions);

        if ($this->db->selectOne($query) !== null) {
            throw new ValidationException(sprintf($this->caseLine, $this->table, implode(',', $this->columns)));
        }
    }
}
