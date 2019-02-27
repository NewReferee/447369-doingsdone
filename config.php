<?php

// Не трогать, обеспечивает работу сессий
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

// Показывать ли выполненные задания
$show_complete_tasks = rand(0, 1);

// Номер текущего пользователя
$current_user = 3;

// URI недоступные для чтения
$lock = ['/index.php'];
?>