<?php
require_once ('functions.php');
require_once ('config.php');

// Работа с базой данных
$connect = database_init ("localhost", "root", "", "doingsdone");

$database_command = 
	'SELECT category_id, category_name
	FROM category_list
	WHERE user_id = ' . intval($current_user) .';';

$category_list = database_read ($connect, $database_command);
xss_protect ($category_list);

$database_command =
	'SELECT tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state
	FROM tasks
	JOIN category_list ON tasks.category_id = category_list.category_id
	WHERE tasks.user_id = ' . intval($current_user) . ';';

$tasks = database_read ($connect, $database_command);
xss_protect ($tasks);

// Шаблонизация
if (!isset($_POST['name'])) {
	$page_content = include_template ('add_project.php', [
			'errors' => []
			]);

	$layout_content = include_template ('layout-logged.php', [
		'tasks' => $tasks,
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $category_list
		]);
}
else if (!empty(add_valid($_POST['name'], null, null, $category_list, 'project'))) {
	$page_content = include_template ('add_project.php', [
			'errors' => add_valid($_POST['name'], null, null, $category_list, 'project')
			]);

	$layout_content = include_template ('layout-logged.php', [
		'tasks' => $tasks,
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $category_list
		]);	
}
else {
	$database_command = 
		'INSERT INTO category_list(category_list.user_id, category_list.category_name)
		VALUES (?, ?);';

	$data_values = [intval($current_user), strval($_POST['name'])];
	$data_types = 'is';

	database_write($connect, $database_command, $data_values, $data_types);	
	header("Location: ./");
}

print ($layout_content)

?>