<?php

spl_autoload_register(function ($class) {
    // Получаем путь к файлу из имени класса
    $path = __DIR__ . "\\". $class . '.php';
    // Если в текущей папке есть такой файл, то выполняем код из него
    if (file_exists($path)) {
        require_once $path;
    }
});