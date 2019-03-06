<?php
require_once ('functions.php');
require_once ('init.php');

$database_command = 
	'SELECT user_id, user_name, user_password, user_email
	FROM users;';

$users = database_read ($connect, $database_command, [], '');
xss_protect ($users);

if (!isset($_POST['password']) && !isset($_POST['email'])) {
	$page_content = include_template ('login.php', [
		'errors' => []
		]);

	$layout_content = include_template ('layout-login.php', [
		'content' => $page_content, 
		'title' => $page_title
		]);
}
else if (!empty(login_valid($users, null, strval($_POST['password']), strval($_POST['email']), 'login'))) {
	$page_content = include_template ('login.php', [
		'errors' => login_valid($users, null, strval($_POST['password']), strval($_POST['email']), 'login')
		]);

	$layout_content = include_template ('layout-login.php', [
		'content' => $page_content, 
		'title' => $page_title
		]);	
}
else {

	$database_command = 
		'SELECT user_id, user_name, user_email
		FROM users
		WHERE user_email = ?;';

	$current_user = database_read ($connect, $database_command, [strval($_POST['email'])], 's');
	$current_user_name = $current_user[0]['user_name'];
	$current_user = $current_user[0]['user_id'];

	$database_command = 
		'SELECT category_id, category_name
		FROM category_list
		WHERE user_id = ?;';

	$category_list = database_read ($connect, $database_command, [intval($current_user)], 'i');
	xss_protect ($category_list);

	$database_command =
		'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
		FROM tasks
		JOIN category_list ON tasks.category_id = category_list.category_id
		WHERE tasks.user_id = ?;';

	$tasks = database_read($connect, $database_command, [intval($current_user)], 'i');
	xss_protect ($tasks);

	$_SESSION = [
		'current_user' => strip_tags($current_user),
		'current_user_name' => strip_tags($current_user_name),
		'category_list' => $category_list,
		'tasks' => $tasks
	];

	header("Location: ./");
	die ();
}

print ($layout_content)

?>