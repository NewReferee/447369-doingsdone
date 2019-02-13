<?php
require_once ('functions.php');
require_once ('config.php');

// Работа с базой данных
$connect = database_init("localhost", "root", "", "doingsdone");
$database_command = 
	'SELECT category_name
	FROM category_list
	WHERE user_id = ' . $current_user .';';

$category_list = database_read($connect, $database_command);
$category_list = array_column($category_list, 'category_name');

$database_command =
	'SELECT task_desc, date_require, 
	(SELECT category_list.category_name
	FROM category_list
	WHERE tasks.category_id = category_list.category_id) AS category_name, task_state
	FROM tasks
	WHERE tasks.user_id = ' . $current_user . ';';

$tasks = database_read($connect, $database_command);

// Дополнительная защита от XSS
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
