<?php
require_once ('functions.php');
require_once ('init.php');

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
	if (isset($_POST['search'])) {
		$_POST['search'] = trim($_POST['search']);
		if (mb_strlen($_POST['search']) >= 3) { // Поиск по задачам
			$database_command =
				'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
				FROM tasks
				JOIN category_list ON tasks.category_id = category_list.category_id
				WHERE tasks.user_id = ? AND MATCH(tasks.task_desc) AGAINST(? IN BOOLEAN MODE)';
			$current_tasks = database_read ($connect, $database_command, [intval($_SESSION['current_user']), strval($_POST['search']) . '*'], 'is');
			xss_protect($current_tasks);
			$tasks_list = get_tasks_list($current_tasks);
			$soon = get_soon ($_SESSION['tasks']);
			date_format_dmy ($_SESSION['tasks']);		
			$page_content = include_template ('index.php', [
				'tasks' => $_SESSION['tasks'],
				'tasks_list' => $tasks_list,
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
				WHERE tasks.user_id = ? AND tasks.task_desc LIKE ?;';			
			$current_tasks = database_read ($connect, $database_command, [intval($_SESSION['current_user']), strval('%' . $_POST['search']) . '%'], 'is');
			xss_protect($current_tasks);
			$tasks_list = get_tasks_list($current_tasks);
			$soon = get_soon ($_SESSION['tasks']);
			date_format_dmy ($_SESSION['tasks']);		
			$page_content = include_template ('index.php', [
				'tasks' => $_SESSION['tasks'],
				'tasks_list' => $tasks_list,
				'show_complete_tasks' => 1,
				'soon' => $soon,
				'domain' => $domain
				]);
		}
	}
	else {
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
				WHERE task_id = ?;';
			$task_checkbox_state = database_read ($connect, $database_command, [intval($_SESSION['tasks'][intval($_GET['task_id'])]['task_id'])], 'i');
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

		if (isset($_GET['sort'])) { // Если задачи надо отсортировать
			switch ($_GET['sort']) {
				case 'today':
					$database_command =
						'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
						FROM tasks
						JOIN category_list ON tasks.category_id = category_list.category_id
						WHERE tasks.user_id = ? AND DAY(tasks.date_require) = DAY(NOW())' . ';';				
				break;
				
				case 'tomorrow':
					$database_command =
						'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
						FROM tasks
						JOIN category_list ON tasks.category_id = category_list.category_id
						WHERE tasks.user_id = ? AND DAY(tasks.date_require) = DAY(NOW() + INTERVAL 1 DAY)' . ';';				
				break;

				case 'expired':
					$database_command =
						'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
						FROM tasks
						JOIN category_list ON tasks.category_id = category_list.category_id
						WHERE tasks.user_id = ? AND tasks.date_require < (NOW() - INTERVAL 1 DAY)' . ';';				
				break;
			}	
		}	
		
		if (!isset($_GET['sort']) || $_GET['sort'] == 'all') { // Если сортировать не надо
		$database_command =
			'SELECT tasks.task_id, tasks.category_id, tasks.task_desc, tasks.date_require, category_list.category_name AS category_name, tasks.task_state, tasks.file_link
			FROM tasks
			JOIN category_list ON tasks.category_id = category_list.category_id
			WHERE tasks.user_id = ?;';
		}

		$current_tasks = database_read ($connect, $database_command, [intval($_SESSION['current_user'])], 'i');
		xss_protect ($current_tasks);
		$tasks_list = get_tasks_list($current_tasks);
		$soon = get_soon ($_SESSION['tasks']);
		date_format_dmy ($_SESSION['tasks']);		
		$page_content = include_template ('index.php', [
			'tasks' => $_SESSION['tasks'],
			'tasks_list' => $tasks_list,
			'show_complete_tasks' => 1,
			'soon' => $soon,
			'domain' => $domain
			]);

		if (!isset($_GET['category_id'])) { // Если требуется показать все задачи
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
				WHERE tasks.category_id = ?;';

			$tasks_current = database_read ($connect, $database_command, [intval($_GET['category_id'])], 'i');
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
