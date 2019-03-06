<?php 
require_once ('init.php');

/**
* Шаблонизация
* @param string $name имя шаблона в папке templates
* @param integer $data данные для заполнения
* @return string $result готовая страница
*/
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

/**
* Инициализация базы данных
* @param string $hostname имя хоста, по умолчанию localhost
* @param string $username имя пользователя, по умолчанию root
* @param string $password пароль
* @param string $servername имя базы данных
* @return string $connect ресурс соединения
*/
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

/**
* Чтение из базы данных
* @param string $connect ресурс соединения
* @param string $database_command обычное или подготовленное выражение SQL
* @param array $data_values данные для подготовленного выражения в виде простого массива
* @param string $data_types строка с типами данных i или s для подготовленного выражения соответсвенно
* @return array $database_assoc ассоциативный массив, где ключ - номер записи, а значение - ассоциативный массив (поле => значение)
*/
function database_read ($connect, $database_command, $data_values, $data_types) {
	if (empty($data_values)) {
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
	else {
		$stmt = mysqli_prepare($connect, $database_command);
		if (!$stmt) {
			print ('Ошибка запроса');
			die();
		}
		$types_array[] = $data_types;
		$data = array_merge($types_array, $data_values);
		mysqli_stmt_bind_param($stmt, ...$data);
		$database_result = mysqli_stmt_execute($stmt);
		$database_result = mysqli_stmt_get_result($stmt);
		if ($database_result) {
			$database_assoc = mysqli_fetch_all ($database_result, MYSQLI_ASSOC);
			return $database_assoc;
		}
		else {
			print ('Ошибка запроса: ' . mysqli_error ($database_result));
			die();
		}
	}
}

/**
* Запись полей в базу данных
* @param string $connect ресурс соединения
* @param string $database_command обычное или подготовленное выражение SQL
* @param array $data_values данные для подготовленного выражения в виде простого массива
* @param string $data_types строка с типами данных i или s для подготовленного выражения соответсвенно
*/
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

/**
* Защита от XSS
* @param array $array изменяет массив по ссылке, устраняя XSS уязвимости
*/
function xss_protect (&$array) {
	if ($array === []) {
		return [];
	}
	foreach ($array as $array_key => $array_values) {
		foreach ($array_values as $key => $value) {
			$array[$array_key][$key] = strip_tags($value);
		}
	}
}

/**
* Подсчёт количества задач для данной категории
* @param array $tasks список задач
* @param string $category название категории
* @return integer $counter количество задач
*/
function get_tasks ($tasks, $category) {
	$counter = 0;
  foreach ($tasks as $task_values) {
      if ($task_values['category_name'] === $category) {
          $counter++;
      }
  }
	return $counter;
}

/**
* Определение срочности задач
* @param array $tasks список задач
* @return array $soon простой массив с значениями типа boolean соответственно задачам
*/
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

/**
* Проверка доступны ли для пользователя запрошенные данные в БД и существуют ли таковые вообще
* @param string $connect ресурс соединения
* @param integer $category_id id проекта в параметрах запроса
* @param string $current_user id пользователя
* @return boolean найдена ли страница
*/
function page_not_found ($connect = null, $category_id = null, $current_user = null) {
	$database_command = 
	'SELECT category_name
	FROM category_list
	WHERE category_id = ? AND user_id = ?;';
	$result = database_read ($connect, $database_command, [intval($category_id), intval($current_user)], 'ii');
	if (empty($result)) {
		return true;
	}
	return false;
}

/**
* Валидация форм добавления проекта и задачи
* @param string $name название проекта или задачи
* @param string $date дата выполнения задачи
* @param integer $current_category id проекта, выбранного при добавлении задачи
* @param array $category_list список категорий
* @param string $type тип формы, либо project, либо task, если project, то 2-й и 3-й параметры не важны
* @return array $errors список ошибок
*/
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

/**
* Валидация форм регистрации и входа
* @param array $users список пользователей
* @param string $name имя пользователя при регистрации
* @param string $password пароль
* @param string $email e-mail
* @param string $type тип формы, либо register, либо login, если login, то 2-й параметр не важен
* @return array $errors список ошибок
*/
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

/**
* Перевод даты выполнения задачи в формат d.m.Y по ссылке
* @param array $tasks список задач
*/
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

/**
* Преобразование ассоциативного массива задач в простой массив с названиями задач
* @param array $array ассоциативный массив с задачами
* @return array $list простой массив с названиями задач
*/
function get_tasks_list ($array) {
	if ($array == []) {
		return [];
	}
	foreach ($array as $array_value) {
		$list[] = $array_value['task_desc'];
	}
	return $list;
}

/**
* Переключение состояния чекбокса на противоположное по ссылке для записи в БД
* @param boolean $task_checkbox_state состояние чекбокса (вкл/выкл)
*/
function toggle_tasks_checkbox (&$task_checkbox_state) {
	if ($task_checkbox_state[0]['task_state'] == 1) {
		$task_checkbox_state[0]['task_state'] = 0;
	}
	else {
		$task_checkbox_state[0]['task_state'] = 1;
	}	
}

/**
* Возврат URL адреса c параметрами для сортировки
* @param string $day параметр сортировки
* @return string $sort_url готовый URL
*/
function get_sort_url ($day) {
	$sort_url = './?sort=' . strval($day);
	return $sort_url;
}

/**
* Возврат URL адреса c параметрами для сортировки
* @param string $message_text сообщение для отправки
* @param string $subject тема сообщения
* @param string $users список пользователей (email => имя)
*/
function email_send ($message_text, $recipients, $subject) {
	$transport = new Swift_SmtpTransport('phpdemo.ru', 25);
	$transport->setUsername('keks@phpdemo.ru');
	$transport->setpassword('htmlacademy');
	$mailer = new Swift_Mailer($transport);
	$message = new Swift_Message();
	$message->setSubject($subject);
	$message->setFrom(['keks@phpdemo.ru' => 'DoingsDone']);
	$message->setBcc($recipients);
	$message->setBody($message_text, 'text/html');
	try {
	$result = $mailer->send($message);
	}
	catch (Exception $e) {
		$result = 0;
	}
}
?>