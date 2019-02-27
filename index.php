<?php
require_once ('functions.php');
require_once ('config.php');

if (page_not_found($lock)) {
	http_response_code(404);
	die();
}

if (!isset($_SESSION['current_user'])) {
	$page_content = include_template ('guest.php', [

				]);
	$layout_content = include_template ('layout-guest.php', [
			'content' => $page_content, 
			'title' => $page_title
			]);

	print ($layout_content);
}
else {
	$connect = database_init ("localhost", "root", "", "doingsdone");	

	if (!isset($_GET['category_id'])) {
		$soon = get_soon ($_SESSION['tasks']);
		date_format_dmy ($_SESSION['tasks']);

		$page_content = include_template ('index.php', [
			'tasks' => $_SESSION['tasks'], 
			'show_complete_tasks' => $show_complete_tasks,
			'soon' => $soon,
			'domain' => $domain
			]);
	}
	else if (!page_not_found($connect, $_GET['category_id'], $_SESSION['current_user'])) {
		$database_command =
			'SELECT tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
			FROM tasks
			JOIN category_list ON tasks.category_id = category_list.category_id
			WHERE tasks.category_id = ' . intval($_GET['category_id']) . ';';

		$tasks_current = database_read ($connect, $database_command);
		xss_protect ($tasks_current);

		$soon = get_soon ($tasks_current);
		date_format_dmy ($tasks_current);

		$page_content = include_template ('index.php', [
			'tasks' => $tasks_current, 
			'show_complete_tasks' => $show_complete_tasks,
			'soon' => $soon,
			'domain' => $domain
			]);	
	}
	else {
		http_response_code(404);
		die();
	}

	$layout_content = include_template ('layout-logged.php', [
		'tasks' => $_SESSION['tasks'],
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $_SESSION['category_list'],
		'current_user_name' => $_SESSION['current_user_name']
		]);

	print ($layout_content);
}
?>
