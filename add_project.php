<?php
require_once ('init.php');

if (!isset($_SESSION['current_user'])) {  // Если сессия пуста, показываем гостевую страницу
	header("Location: ./");
	die ();
} 

if (!isset($_POST['name'])) {
	$page_content = include_template ('add_project.php', [
			'errors' => []
			]);

	$layout_content = include_template ('layout-logged.php', [
		'tasks' => $_SESSION['tasks'],
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $_SESSION['category_list'],
		'current_user_name' => $_SESSION['current_user_name']
		]);
}
else if (!empty(add_valid($_POST['name'], null, null, $_SESSION['category_list'], 'project'))) {
	$page_content = include_template ('add_project.php', [
			'errors' => add_valid($_POST['name'], null, null, $_SESSION['category_list'], 'project')
			]);

	$layout_content = include_template ('layout-logged.php', [
		'tasks' => $_SESSION['tasks'],
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $_SESSION['category_list'],
		'current_user_name' => $_SESSION['current_user_name']
		]);	
}
else {
	$database_command = 
		'INSERT INTO category_list(category_list.user_id, category_list.category_name)
		VALUES (?, ?);';

	$data_values = [intval($_SESSION['current_user']), strval(strip_tags($_POST['name']))];
	$data_types = 'is';

	database_write($connect, $database_command, $data_values, $data_types);	

	$database_command = 
		'SELECT category_id, category_name
		FROM category_list
		WHERE user_id = ?;';

	$category_list = database_read($connect, $database_command, [intval($_SESSION['current_user'])], 'i');

	$_SESSION['category_list'] = $category_list;

	header("Location: ./");
	die ();
}

print ($layout_content)

?>