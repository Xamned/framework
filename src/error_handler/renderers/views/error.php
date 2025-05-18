<?php

/**
 * @var string $envMode
 * @var Throwable $e
 * @var string $debugTag
 */

use xamned\framework\ExecutionTypeEnum;

$traces= array_filter($e->getTrace(), function($item) {
    return count($item) >= 6;
});

$showErrorCode = $envMode === ExecutionTypeEnum::DEVELOPMENT->value && $e->getCode() !== 0;
?>

<style>
    .errorBody {
        display: inline-block;
        background: #ffb0c7;
        padding: 1%;
    }

    .errorMessage {
        font-weight: bold;
        font-size: 80%;
    }
</style>

<div class="">
<div class="errorBody">
    <p>Запрос не может быть обработан <br>
    <?php if($showErrorCode === true): ?>
    Ошибка: <?= $e->getCode() ?> <br>
    <?= $e->getMessage() ?> </p>
    <p class="errorMessage">идентификатор сеанса: <?= $debugTag ?></p>
    <?php endif; ?>
    <?php if($showErrorCode === false): ?>
    Произошла внутренняя ошибка сервера</p>
    <p class="errorMessage">Обратитесь к администратору системы <a href="mailto:support@efko.ru">support@efko.ru</a><br>
    В запросе укажите идентификатор сеанса, идентификатор сеанса: <?= $debugTag ?></p>
    <?php endif; ?>
</div>
<?php if($envMode === ExecutionTypeEnum::DEVELOPMENT->value): ?>
<h3>Трейс вызова</h3>
<div class="errorBody errorMessage">
    <?= preg_replace("/(:)/", "$1<br />", nl2br(nl2br($e->getTraceAsString()))); ?>
</div>
    <?php endif; ?>
</div>
