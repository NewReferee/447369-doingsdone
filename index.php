<?php
require_once ('functions.php');
require_once ('config.php');

if (page_not_found($lock)) {
	http_response_code(404);
	die();
}

if (!isset($_SESSION['current_user'])) {  // Если сессия пуста, показываем гостевую страницу
	$page_content = include_template ('guest.php', [

		]);
	$layout_content = include_template ('layout-guest.php', [
		'content' => $page_content, 
		'title' => $page_title
		]);

	print ($layout_content);
} 
else { // Если сессия есть, показываем главную страницу
	$connect = database_init ("localhost", "root", "", "doingsdone");

	if (isset($_POST['search'])) {
		if (mb_strlen($_POST['search']) >= 3) { // Поиск по задачам
			$database_command =
				'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
				FROM tasks
				JOIN category_list ON tasks.category_id = category_list.category_id
				WHERE tasks.user_id = ' . intval($_SESSION['current_user']) . ' AND MATCH(tasks.task_desc) AGAINST(\'' . $_POST['search'] . '*\' IN BOOLEAN MODE);';
			$_SESSION['tasks'] = database_read ($connect, $database_command);
			$soon = get_soon ($_SESSION['tasks']);
			date_format_dmy ($_SESSION['tasks']);		
			$page_content = include_template ('index.php', [
				'tasks' => $_SESSION['tasks'],
				'tasks_list' => get_tasks_list($_SESSION['tasks']),
				'show_complete_tasks' => 1,
				'soon' => $soon,
				'domain' => $domain
				]);
		}

		if ((empty($_SESSION['tasks']) || mb_strlen($_POST['search']) < 3)) {
			$database_command =
				'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
				FROM tasks
				JOIN category_list ON tasks.category_id = category_list.category_id
				WHERE tasks.user_id = ' . intval($_SESSION['current_user']) . ' AND tasks.task_desc LIKE \'%' . strval($_POST['search'] . '%\';');			
			$_SESSION['tasks'] = database_read ($connect, $database_command);
			$soon = get_soon ($_SESSION['tasks']);
			date_format_dmy ($_SESSION['tasks']);		
			$page_content = include_template ('index.php', [
				'tasks' => $_SESSION['tasks'],
				'tasks_list' => get_tasks_list($_SESSION['tasks']),
				'show_complete_tasks' => 1,
				'soon' => $soon,
				'domain' => $domain
				]);
		}
	}
	else {
		if (isset($_GET['sort'])) { // Если задачи надо отсортировать
			switch ($_GET['sort']) {
				case 'today':
					$database_command =
						'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
						FROM tasks
						JOIN category_list ON tasks.category_id = category_list.category_id
						WHERE tasks.user_id = ' . intval($_SESSION['current_user']) . ' AND DAY(tasks.date_require) = DAY(NOW())' . ';';				
				break;
				
				case 'tomorrow':
					$database_command =
						'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
						FROM tasks
						JOIN category_list ON tasks.category_id = category_list.category_id
						WHERE tasks.user_id = ' . intval($_SESSION['current_user']) . ' AND DAY(tasks.date_require) = DAY(NOW() + INTERVAL 1 DAY)' . ';';				
				break;

				case 'expired':
					$database_command =
						'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
						FROM tasks
						JOIN category_list ON tasks.category_id = category_list.category_id
						WHERE tasks.user_id = ' . intval($_SESSION['current_user']) . ' AND tasks.date_require < (NOW() - INTERVAL 1 DAY)' . ';';				
				break;
			}	
		}
		
		if (!isset($_GET['sort']) || $_GET['sort'] == 'all') { // Если сортировать не надо
		$database_command =
			'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
			FROM tasks
			JOIN category_list ON tasks.category_id = category_list.category_id
			WHERE tasks.user_id = ' . intval($_SESSION['current_user']) . ';';
		}

		$_SESSION['tasks'] = database_read ($connect, $database_command);
		xss_protect ($_SESSION['tasks']);

		if (!isset($_GET['show_completed']) || ($_GET['show_completed'] == 1)) {
			$show_complete_tasks = 1;
		}
		else {
			$show_complete_tasks = 0;
		}

		if (isset($_GET['task_id'])) { // Смена статуса выполнения задания
			$database_command =
				'SELECT task_state
				FROM tasks
				WHERE task_id = ' . intval($_SESSION['tasks'][intval($_GET['task_id'])]['task_id']) . ';';
			$task_checkbox_state = database_read ($connect, $database_command);
			toggle_tasks_checkbox ($task_checkbox_state);

		$database_command =
				'UPDATE tasks
				SET tasks.task_state = ?
				WHERE task_id = ' . intval($_SESSION['tasks'][intval($_GET['task_id'])]['task_id']) . ';';
		$data_values = [intval($task_checkbox_state[0]['task_state'])];
		$data_types = 'i';
		database_write($connect, $database_command, $data_values, $data_types);
		$_SESSION['tasks'][intval($_GET['task_id'])]['task_state'] = $task_checkbox_state[0]['task_state'];
		}

		if (!isset($_GET['category_id'])) { // Если требуется показать все задачи
			$tasks_list = get_tasks_list($_SESSION['tasks']);

			$soon = get_soon ($_SESSION['tasks']);
			date_format_dmy ($_SESSION['tasks']);

			$page_content = include_template ('index.php', [
				'tasks' => $_SESSION['tasks'],
				'tasks_list' => $tasks_list,
				'show_complete_tasks' => $show_complete_tasks,
				'soon' => $soon,
				'domain' => $domain
				]);
		}
		else if (!page_not_found($connect, $_GET['category_id'], $_SESSION['current_user'])) { // Если надо показать только одну категорию
			$database_command =
				'SELECT tasks.task_desc
				FROM tasks
				WHERE tasks.category_id = ' . intval($_GET['category_id']) . ';';

			$tasks_current = database_read ($connect, $database_command);
			xss_protect ($tasks_current);
			$tasks_list = get_tasks_list($tasks_current);

			$soon = get_soon ($_SESSION['tasks']);
			date_format_dmy ($_SESSION['tasks']);

			$page_content = include_template ('index.php', [
				'tasks' => $_SESSION['tasks'],
				'tasks_list' => $tasks_list,
				'show_complete_tasks' => $show_complete_tasks,
				'soon' => $soon,
				'domain' => $domain
				]);	
		}
		else { // Если в сессии нет данного параметра проекта, страница не найдена
			http_response_code(404);
			die();
		}
	}

	$layout_content = include_template ('layout-logged.php', [ // Подключение главного шаблона
		'tasks' => $_SESSION['tasks'],
		'content' => $page_content, 
		'title' => $page_title, 
		'category_list' => $_SESSION['category_list'],
		'current_user_name' => $_SESSION['current_user_name']
		]);
	print ($layout_content);
}
?>
