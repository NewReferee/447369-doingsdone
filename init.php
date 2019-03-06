<?php
require_once ('functions.php');

// Подключение к базе данных
$connect = database_init ("localhost", "root", "", "doingsdone");

// Обеспечивает работу библиотек
require_once ('vendor/autoload.php');

// Обеспечивает работу сессий
session_start();

// Кодировка
mb_internal_encoding("UTF-8");

// Время жизни Cookies, отправленных в браузер клиента
ini_set('sessions.cookie_lifetime',86400);

// Время жизни сессии
ini_set('sessions.gc_maxlifetime', 86400);

// Адрес сервера
$domain = 'http://447369-doingsdone/';

// Название сайта
$page_title = 'Дела в порядке';
?>