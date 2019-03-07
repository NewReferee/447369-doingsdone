<?php
require_once ('init.php');

$database_command = 
	'SELECT tasks.user_id, users.user_name AS user_name, users.user_email AS user_email, tasks.task_desc, tasks.date_require
	FROM tasks
	JOIN users ON tasks.user_id = users.user_id
	WHERE DAY(tasks.date_require) = DAY(NOW()) AND MONTH(tasks.date_require) = MONTH(NOW()) AND YEAR(tasks.date_require) = YEAR(NOW());';

$users = database_read($connect, $database_command, [], '');

$recipients = [];

foreach ($users as $user_number => $user_value) {
	$recipients[$user_value['user_email']] = $user_value['user_name'];
}

$message_text = '<p class="text">Уважаемый пользователь! <b class="sitename">DOINGSDONE</b> напоминает, что сегодня у вас есть задачи, которые требуют незамедлительного решения!';
$message_text = include_template ('message_notify.php', [
	'message_text' => $message_text
	]);

email_send (strval($message_text), $recipients, 'Напоминание');
header("Location: index.php");

?>