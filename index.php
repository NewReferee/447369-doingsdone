<?php
require_once ('functions.php');
$page_title = 'Дела в порядке';
$show_complete_tasks = rand(0, 1);
$category_list = ['Входящие', 'Учеба', 'Работа', 'Домашние дела', 'Авто'];
$tasks = [
	'task_1' => [
			'desc' => 'Собеседование в IT компании',
			'date' => '09.02.2019',
			'category' => 'Работа',
			'state' => false
	],

	'task_2' => [
			'desc' => 'Выполнить тестовое задание',
			'date' => '25.12.2019',
			'category' => 'Работа',
			'state' => false
	],

	'task_3' => [
			'desc' => 'Сделать задание первого раздела',
			'date' => '09.02.2019',
			'category' => 'Учёба',
			'state' => true
	],

	'task_4' => [
			'desc' => 'Встреча с другом',
			'date' => '22.12.2019',
			'category' => 'Входящие',
			'state' => false
	],

	'task_5' => [
			'desc' => 'Купить корм для кота',
			'date' => 'Нет',
			'category' => 'Домашние дела',
			'state' => false
	],

	'task_6' => [
			'desc' => 'Заказать пиццу',
			'date' => 'Нет',
			'category' => 'Домашние дела',
			'state' => false
	],
];

// Защита от XSS

xss_protect ($tasks);

// Работа со временем
$soon = get_soon($tasks);

// Шаблонизация

$page_content = include_template ('index.php', [
	'tasks' => $tasks, 
	'show_complete_tasks' => $show_complete_tasks,
	'soon' => $soon
	]);

$layout_content = include_template ('layout.php', [
	'tasks' => $tasks,
	'content' => $page_content, 
	'title' => $page_title, 
	'category_list' => $category_list
	]);

print($layout_content)
?>
