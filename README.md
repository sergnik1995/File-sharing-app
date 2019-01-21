File sharing app
======
Реализация файлообменника с помощью PHP 7.2.8, PostgreSQL 11 и Slim 2.0.

Необходимо
------
1. Операционная система Windows
2. Веб-сервер apache 2.4 настроенный для использования с PHP
3. Сервер PostgreSQL
4. Composer

Установка
------
1. Установить в качестве document root директории веб-сервера директорию public
2. Править файл [/app/db.ini.config](/app/db.ini.config) для работы с вашей базой данных и переименовать его в db.ini.
3. Создать таблицу по примеру [files.sql](files.sql) (имя таблицы обязательно должно быть files).
4. Скачать пакеты из [composer.json](composer.json) с помощью composer'a.

Функционал и особенности
------
1. Добавление файлов.
2. Скачивание добавленных файлов.
3. Проигрывание аудио и видеофайлов.
4. Просмотр превью изображений.
5. Просмотр последних ста добавленных файлов на файлообменник.
6. Юзер имеет доступ к своему последнему загруженному на сервер файлу.