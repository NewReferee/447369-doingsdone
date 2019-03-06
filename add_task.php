<?php
require_once ('functions.php');
require_once ('init.php');

if (!isset($_POST['project'])) {
	$page_content = include_template ('add_task.php', [
			'category_list' => $_SESSION['category_list'],
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
else if (!empty(add_valid($_POST['name'], $_POST['date'], intval($_POST['project']), $_SESSION['category_list'], 'task'))) {
	$page_content = include_template ('add_task.php', [
			'category_list' => $_SESSION['category_list'],
			'errors' => add_valid($_POST['name'], $_POST['date'], intval($_POST['project']), $_SESSION['category_list'], 'task')
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
	$filelink = null;
	if (isset($_FILES['preview'])) {
		if (!empty($_FILES['preview']['name'])) {
			$filename = uniqid() . $_FILES['preview']['name'];
			$filelink = 'userfiles/' . $filename;
			move_uploaded_file($_FILES['preview']['tmp_name'], $filelink);
		}
	}	
	
	$now = date('Y-m-d');
	$selected_date = date('Y-m-d', strtotime($_POST['date']));

	$database_command = 
		'INSERT INTO tasks(tasks.category_id, tasks.user_id, tasks.task_desc, tasks.date_create, tasks.date_require, tasks.file_link)
		VALUES (?, ?, ?, ?, ?, ?);';

	$data_values = [intval($_POST['project']), intval($_SESSION['current_user']), strval($_POST['name']), strval($now), strval($selected_date), strval($filelink)];
	$data_types = 'iissss';	

	database_write($connect, $database_command, $data_values, $data_types);

	$database_command =
		'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
		FROM tasks
		JOIN category_list ON tasks.category_id = category_list.category_id
		WHERE tasks.user_id = ?;';

	$tasks = database_read($connect, $database_command, [intval($_SESSION['current_user'])], 'i');
	xss_protect($tasks);
	
	$_SESSION['tasks'] = $tasks;

	header("Location: ./");
	die ();
}

print ($layout_content)
?>