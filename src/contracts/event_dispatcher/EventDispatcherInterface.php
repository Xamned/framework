<?php

namespace xamned\framework\contracts\event_dispatcher;

use xamned\framework\event_dispatcher\Message;

interface EventDispatcherInterface
{
    /**
     * Конфигурирует EventDispatcher с использованием предоставленного массива конфигурации
     *
     * @param array $config массив конфигурации,
     * где каждый элемент представляет собой массив вида
     * ['SOME_EVENT_NAME' => [BarObserver::class, BazObzerver::class]]
     * @return void
     */
    function configure(array $config): void;

    /**
     * Подписывает наблюдателя к определенному событию
     *
     * @param string $eventName имя события, к которому присоединяется наблюдатель
     * @param string $observer класс наблюдателя
     * @return void
     */
    function attach(string $eventName, string $observer): void;

    /**
     * Отписывает наблюдателя от определенного события
     *
     * @param string $eventName имя события, от которого отписывается наблюдатель
     * @param string $observer класс наблюдателя
     * @return void
     */
    function detach(string $eventName, string $observer): void;

    /**
     * Запускает событие и уведомляет соответствующего наблюдателя с переданным сообщением
     *
     * @param string $eventName Имя события, которое будет запущено
     * @param Message $message Сообщение, передаваемое наблюдателю
     * @return void
     */
    function trigger(string $eventName, Message $message): void;
}
