<?php

namespace framework\event_dispatcher;

use framework\contracts\container\ContainerInterface;
use framework\contracts\event_dispatcher\EventDispatcherInterface;
use framework\contracts\event_dispatcher\ObserverInterface;
use Exception;

class EventDispatcher implements EventDispatcherInterface
{
    protected array $events = [];

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function configure(array $config): void
    {
        foreach ($config as $event => $observers) {
            foreach ($observers as $observer) {
                $this->attach($event, $observer);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attach(string $eventName, string $observer): void
    {
        if (is_subclass_of($observer, ObserverInterface::class) === false) {
            throw new Exception("$observer должен реализовывать " . ObserverInterface::class);
        }

        $this->events[$eventName][] = $observer;
    }

    /**
     * {@inheritdoc}
     */
    public function detach(string $eventName, string $observer): void
    {
        $key = array_search($observer, $this->events[$eventName]);

        if ($key !== false) {
            unset($this->events[$eventName][$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function trigger(string $eventName, Message $message): void
    {
        if (isset($this->events[$eventName]) === false) {
            return;
        }
        
        foreach ($this->events[$eventName] as $observer) {
            $this->container->get($observer)->observe($message);
        }
    }
}
