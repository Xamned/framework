<?php

namespace xamned\framework\container;

use xamned\framework\contracts\container\ContainerInterface;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionUnionType;

final class DIContainer implements ContainerInterface
{
    private array $singletons = [];

    private array $definitions = [];

    private static $selfInstance = null;

    protected function __construct(
        private array $config = []
    ) {
        $this->config['singletons'][ContainerInterface::class] = $this;
        $this->singletons = $this->config['singletons'] ?? [];
        $this->definitions = $this->config['definitions'] ?? [];
    }

    /**
     * Запрещает клонирование объекта, являющегося синглтоном
     *
     * @throws LogicException
     */
    public function __clone(): void
    {
        throw new LogicException('Клонирование запрещено');
    }

    /**
     * Именованный конструктор
     * Создает экземпляр класса DIContainer
     *
     * @param array $config Массив конфигурации
     * @return DIContainer экземпляр класса DIContainer
     */
    public static function create(array $config = []): self
    {
        if (self::$selfInstance === null) {
            self::$selfInstance = new self($config);

            return self::$selfInstance;
        }

        throw new LogicException('Повторное создание контейнера запрещено');
    }

    /**
     * {@inheritdoc}
     */
    public function build(string|callable $dependencyName, array $args = []): object
    {
        if (is_callable($dependencyName) === true) {
            return $dependencyName($this);
        }

        $reflection = new ReflectionClass($dependencyName);
        $parameters = $reflection->getConstructor()?->getParameters() ?? [];
        $dependencies = [];

        foreach ($parameters as $parameter) {
            if (isset($args[$parameter->name]) === true) {
                $dependencies[$parameter->name] = $args[$parameter->name];
                continue;
            }

            if ($this->isDependency($parameter) === true) {
                $dependencies[$parameter->name] = $this->get($parameter->getType()->getName());
            }
        }

        return $reflection->newInstanceArgs(array_merge($dependencies, $args));
    }

    /**
     * {@inheritdoc}
     * @throws DependencyNotFoundException
     */
    public function get(string $id): object
    {
        if ($this->has($id) === false && class_exists($id) === true) {
            return $this->build($id);
        }

        if ($this->has($id) === false) {
            throw new DependencyNotFoundException("Зависимость {$id} не существует");
        }

        if ($this->hasDefinition($id) === true) {
            return $this->build($this->definitions[$id]);
        }

        if (is_string($this->singletons[$id]) === true
        || is_callable($this->singletons[$id]) === true) {
            $this->singletons[$id] = $this->build($this->singletons[$id]);
        }

        return $this->singletons[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function call(object|string $handler, string $method, array $args = []): mixed
    {
        if (is_string($handler) === true) {
            $handler = $this->get($handler);
        }

        $reflection = new ReflectionMethod($handler, $method);

        $parameters = [];

        foreach ($reflection->getParameters() as $parameter) {
            if ($this->isDependency($parameter) === true) {
                $parameters[] = $this->get($parameter->getType()->getName());
            }
        }

        return $reflection->invokeArgs($handler, array_merge($args, $parameters));
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $id): bool
    {
        return $this->hasSingleton($id) === true || $this->hasDefinition($id) === true;
    }

    public function hasSingleton(string $id): bool
    {
        return isset($this->singletons[$id]);
    }

    public function hasDefinition(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    public function attach(string $dependencyName, mixed $dependency): void
    {
        $this->definitions[$dependencyName] = $dependency;
    }

    public function attachSingleton(string $dependencyName, mixed $dependency): void
    {
        $this->singletons[$dependencyName] = $dependency;
    }

    private function isDependency(ReflectionParameter $parameter): bool
    {
        if ($parameter->getType() instanceof ReflectionUnionType) {
            return false;
        }

        if ($parameter->getType()?->isBuiltin() === true) {
            return false;
        }

        if ($parameter->getType()?->isBuiltin() === null) {
            return false;
        }

        if (is_array($parameter) === true) {
            return false;
        }

        if ($parameter->isDefaultValueAvailable() === true) {
            return false;
        }

        return true;
    }
}
