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

// Подсчет количества задач для данной категории

function get_tasks ($tasks, $category) {
$counter = 0;
  foreach ($tasks as $task_value) {
      if ($task_value['category'] === $category) {
          $counter++;
      }
  }
return $counter;
}
?>