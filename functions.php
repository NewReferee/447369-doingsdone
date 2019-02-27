<?php 
require_once ('config.php');

// Шаблонизатор
function include_template ($name, $data) {
	$name = 'templates/' . $name;
	$result = '';

	if (!is_readable ($name)) {
			return $result;
	}

	ob_start();
	extract ($data);
	require $name;

	$result = ob_get_clean();

	return $result;
}

// Подключение к базе данных и возврат ресурса подключения
function database_init ($hostname, $username, $password, $servername) {
	$connect = mysqli_connect ($hostname, $username, $password, $servername);
	if (!$connect) {
		print ('Ошибка подключения: ' . mysqli_connect_error ());
		die ();
	}
	else {
	mysqli_set_charset ($connect, "utf8");
	return $connect;
	}
}

// Чтение базы данных и возврат ассоциативного массива, где ключ - номер записи, а значение - ассоциативный массив (поле => значение)
function database_read ($connect, $database_command) {
	$database_result = mysqli_query ($connect, $database_command);
	if ($database_result) {
		$database_assoc = mysqli_fetch_all ($database_result, MYSQLI_ASSOC);
		return $database_assoc;
	}
	else {
		print ('Ошибка запроса: ' . mysqli_error ($database_result));
		die();
	}
}

// Запись полей в базу данных
function database_write ($connect, $database_command, $data_values, $data_types) {	
	$stmt = mysqli_prepare($connect, $database_command);
	if (!$stmt) {
		print ('Ошибка запроса');
		die();
	}	
	$types_array[] = $data_types;
	$data = array_merge($types_array, $data_values);
	mysqli_stmt_bind_param($stmt, ...$data);
	mysqli_stmt_execute($stmt);
}

// Защита от XSS
function xss_protect (&$array) {
	if ($array === []) {
		return [];
	}
	foreach ($array as $array_key => $array_values) {
		foreach ($array_values as $key => $value) {
			$array[$array_key][$key] = htmlspecialchars ($value);
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
	if ($tasks === []) {
		return [];
	}
	date_default_timezone_set('Europe/Moscow');
	foreach ($tasks as $task_values) {
		$time_left = (strtotime ($task_values['date_require']) - time ()) / 3600;
		if ($task_values['date_require'] !== "" && !$task_values['task_state'] && $time_left <= 24) {
			$soon[] = true;
		}
		else {
			$soon[] = false;
		}
	}
	return $soon;
}

// Если на вход ничего не подается, то проверяет доступен ли URI для чтения, если подается, то проверяет корректность параметров GET
function page_not_found ($lock = null, $connect = null, $category_id = null, $current_user = null) {
	if ($lock !== null) {
		foreach ($lock as $key => $value) {
			if ($_SERVER['REQUEST_URI'] === $value) {
				return true;
			}
		}
	}
	else {
		$database_command = 
		'SELECT category_name
		FROM category_list
		WHERE category_id = ' . intval($category_id) . ' AND user_id = ' . intval($current_user) . ';';
		$result = database_read ($connect, $database_command);
		if (empty($result)) {
			return true;
		}
		return false;
	}
}

// Валидация формы добавления проекта и задачи (type - либо project, либо task, если project, то 2-й и 3-й параметры не важны)
function add_valid ($name, $date, $current_category, $category_list, $type) {
	$errors = [];
	switch ($type) {
		case 'project':
			if (empty($name)) {
				$errors [] = 'empty-name';
				return $errors;
			}
			foreach ($category_list as $category_value) {
				if (mb_strlen($name, 'utf8') === mb_strlen($category_value['category_name'], 'utf8') && mb_stristr($name, $category_value['category_name'], false, 'utf8') === $name) {				
					$errors [] = 'exist-category';
				}
			}
			return $errors;
		break;
		
		case 'task':
			date_default_timezone_set('Europe/Moscow');
			if (!strtotime($date) || strtotime($date) + 86400 - time () < 0) {
				$errors [] = 'invalid-date';
			}
			if (empty($name)) {
				$errors [] = 'empty-name';
			}
			foreach ($category_list as $category_value) {
				if ($current_category === $category_value['category_id']) {
					return $errors;
				}
			}
			$errors [] = 'invalid-category';
			return $errors;
		break;
	}
}

// Валидация формы регистрации и входа (type - либо register, либо login, если login, то 2-й параметр не важен)
function login_valid ($users, $name, $password, $email, $type) {
	$errors = [];
	switch ($type) {
		case 'register':
			if (empty($name)) {
				$errors [] = 'empty-name';
			}
			else {
				foreach ($users as $user_value) {
					if ($user_value['user_name'] === $name) {
						$errors [] = 'exist-name';
					}
				}
			}
			if (empty($password)) {
				$errors [] = 'empty-password';
			}
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors [] = 'invalid-email';
			}
			else {
				foreach ($users as $user_value) {
					if ($user_value['user_email'] === $email) {
						$errors [] = 'exist-email';
					}
				}
			}
		break;
		case 'login':
			$errors [] = 'invalid-password';
			if (empty($password)) {
				$errors [] = 'empty-password';
				$errors = array_diff($errors, ['invalid-password']);
			}
			else {
				foreach ($users as $user_value) {
					if (password_verify($password, $user_value['user_password'])) {
						$errors = array_diff($errors, ['invalid-password']);
					}
				}
			}
			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$errors [] = 'invalid-email';
			}
		break;
	}
	return $errors;
}

function date_format_dmy (&$tasks) {
	if ($tasks === []) {
		return [];
	}
	foreach ($tasks as $task_number => $task_value) {
		if ($task_value['date_require'] !== "") {
			$tasks[$task_number]['date_require'] = date('d.m.Y', strtotime($task_value['date_require']));
		}
	}
}
?>