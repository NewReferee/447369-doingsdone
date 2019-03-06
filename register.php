<?php
require_once ('functions.php');
require_once ('init.php');

$database_command = 
	'SELECT user_id, user_name, user_password, user_email
	FROM users;';

$users = database_read ($connect, $database_command, [], '');
xss_protect ($users);

if (!isset($_POST['name']) && !isset($_POST['password']) && !isset($_POST['email'])) {
	$page_content = include_template ('register.php', [
		'errors' => []
		]);

	$layout_content = include_template ('layout-login.php', [
		'content' => $page_content, 
		'title' => $page_title
		]);
}
else if (!empty(login_valid($users, $_POST['name'], $_POST['password'], $_POST['email'], 'register'))) {
	$page_content = include_template ('register.php', [
		'errors' => login_valid($users, $_POST['name'], $_POST['password'], $_POST['email'], 'register')
		]);

	$layout_content = include_template ('layout-login.php', [
		'content' => $page_content, 
		'title' => $page_title
		]);	
}
else {
	$_POST['password'] = password_hash (strval($_POST['password']), PASSWORD_DEFAULT);
	$now = date('Y-m-d');
	$database_command = 
		'INSERT INTO users(users.user_name, users.user_password, users.user_email, users.date_register)
		VALUES (?, ?, ?, ?);';

	$data_values = [strval($_POST['name']), strval($_POST['password']), strval($_POST['email']), strval($now)];
	$data_types = 'ssss';	

	database_write($connect, $database_command, $data_values, $data_types);

	$recipients = [];
	$recipients[$_POST['email']] = $_POST['name'];
	$message_text = '<p class="text">Здравствуйте, <u class="username">' . strval($_POST['name']) . '</u>. Регистрация на <b class="sitename">DOINGSDONE</b> прошла успешно, наслаждайтесь! ;)</p class="text">';
	$message_text = include_template ('message_register.php', [
		'message_text' => $message_text
		]);
	email_send (strval($message_text), $recipients, 'Регистрация на сайте');

	header("Location: login.php");
	die ();
}

print ($layout_content)
?>