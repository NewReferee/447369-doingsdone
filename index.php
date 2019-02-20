<?php
require_once ('functions.php');
require_once ('config.php');

if (page_not_found($lock)) {
	http_response_code(404);
	die();	
}
// Работа с базой данных
$connect = database_init ("localhost", "root", "", "doingsdone");

$database_command = 
	'SELECT category_id, category_name
	FROM category_list
	WHERE user_id = ' . $current_user .';';

$category_list = database_read ($connect, $database_command);

$database_command =
	'SELECT tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state
	FROM tasks
	JOIN category_list ON tasks.category_id = category_list.category_id
	WHERE tasks.user_id = ' . $current_user . ';';

$tasks = database_read ($connect, $database_command);

// Дополнительная защита от XSS
xss_protect ($tasks);

// Работа со временем
$soon = get_soon ($tasks);

// Шаблонизация
if (!isset($_GET['category_id'])) {
	$page_content = include_template ('index.php', [
		'tasks' => $tasks, 
		'show_complete_tasks' => $show_complete_tasks,
		'soon' => $soon,
		'category_id' => "all"
		]);
}
else if (!page_not_found($connect, $_GET['category_id'], $current_user)) {
	$page_content = include_template ('index.php', [
		'tasks' => $tasks, 
		'show_complete_tasks' => $show_complete_tasks,
		'soon' => $soon,
		'category_id' => $_GET['category_id']
		]);	
}
else {
	http_response_code(404);
	die();
}

$layout_content = include_template ('layout.php', [
	'tasks' => $tasks,
	'content' => $page_content, 
	'title' => $page_title, 
	'category_list' => $category_list
	]);

print ($layout_content)
?>
