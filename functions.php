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
      if ($task_values['category'] === $category) {
          $counter++;
      }
  }
	return $counter;
}

// Срочность задач

function get_soon ($tasks) {
	date_default_timezone_set('Europe/Moscow');
	foreach ($tasks as $task_values) {
		$time_left = (strtotime($task_values['date']) - time()) / 3600;
		if ($task_values['date']	!==	'Нет' && $time_left <= 24) {
			$soon[] = true;
		}
		else {
			$soon[] = false;
		}
	}
	return $soon;
}
?>