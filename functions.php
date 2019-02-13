<?php 

// Шаблонизатор
function include_template($name, $data) {
	$name = 'templates/' . $name;
	$result = '';

	if (!is_readable($name)) {
			return $result;
	}

	ob_start();
	extract($data);
	require $name;

	$result = ob_get_clean();

	return $result;
}

// Подключение к базе данных и возврат ресурса подключения
function database_init ($hostname, $username, $password, $servername) {
	$connect = mysqli_connect($hostname, $username, $password, $servername);
	if (!$connect) {
		print ('Ошибка подключения: ' . mysqli_connect_error());
	}
	else {
	mysqli_set_charset($connect, "utf8");
	return $connect;
	}
}

// Чтение базы данных и возврат ассоциативного массива, где ключ - номер записи, а значение - ассоциативный массив (поле => значение)
function database_read ($connect, $database_command) {
	$database_result = mysqli_query($connect, $database_command);
	if ($database_result) {
		$database_assoc = mysqli_fetch_all($database_result, MYSQLI_ASSOC);
		return $database_assoc;
	}
	else {
		print ('Ошибка запроса: ' . mysqli_error($database_result));
	}
}

// Защита от XSS
function xss_protect (&$tasks) {
	foreach ($tasks as $task_key => $task_values) {
		foreach ($task_values as $key => $value) {
			$tasks[$task_key][$key] = htmlspecialchars($value);
		}
	}
}

// Количества задач для данной категории
function get_tasks ($tasks, $category) {
	$counter = 0;
  foreach ($tasks as $task_values) {
      if ($task_values['category_name'] === $category) {
          $counter++;
      }
  }
	return $counter;
}

// Срочность задач
function get_soon ($tasks) {
	date_default_timezone_set('Europe/Moscow');
	foreach ($tasks as $task_values) {
		$time_left = (strtotime($task_values['date_require']) - time()) / 3600;
		if ($task_values['date_require'] != null && $time_left <= 24) {
			$soon[] = true;
		}
		else {
			$soon[] = false;
		}
	}
	return $soon;
}
?>