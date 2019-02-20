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

$database_command =
	'SELECT tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state
	FROM tasks
	JOIN category_list ON tasks.category_id = category_list.category_id
	WHERE tasks.user_id = ' . intval($current_user) . ';';

$tasks = database_read ($connect, $database_command);

// Дополнительная защита от XSS
xss_protect ($tasks);

// Шаблонизация
if (!isset($_POST['project'])) {
	$page_content = include_template ('add.php', [
			'category_list' => $category_list,
			'errors' => []
		]);

	$layout_content = include_template ('layout.php', [
		'tasks' => $tasks,
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $category_list
		]);
}
else if (!empty(form_valid($_POST['name'], $_POST['date'], $_POST['project'], $category_list))) {
	$page_content = include_template ('add.php', [
			'category_list' => $category_list,
			'errors' => form_valid($_POST['name'], $_POST['date'], $_POST['project'], $category_list)
		]);

	$layout_content = include_template ('layout.php', [
		'tasks' => $tasks,
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $category_list
		]);
}
else {
	$filelink = null;
	if (isset($_FILES['preview'])) {
		$filename = uniqid() . $_FILES['preview']['name'];
		$filelink = 'userfiles/' . $filename;
		move_uploaded_file($_FILES['preview']['tmp_name'], $filelink);
	}
	$database_command = 
		'INSERT INTO tasks(tasks.category_id, tasks.user_id, tasks.task_desc, tasks.date_create, tasks.date_require, tasks.file_link)
		VALUES
		(' . intval($_POST['project']) . ', ' . intval($current_user) . ', ' . '\'' . $_POST['name'] . '\'' . ', ' . '\'' . date('Y-m-d') . '\'' . ', ' . '\'' . date('Y-m-d', strtotime($_POST['date'])) . '\'' . ', ' . '\'' . $filelink . '\'' . ');';
	database_write ($connect, $database_command);
	header("Location: ./");
}

print ($layout_content)
?>