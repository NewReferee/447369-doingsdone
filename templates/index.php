<?php
require_once ('functions.php');
require_once ('init.php');
?>

<h2 class="content__main-heading">Список задач</h2>

<form class="search-form" action="index.php" method="post">
	<input class="search-form__input" type="text" name="search" value="" placeholder="Поиск по задачам">

	<input class="search-form__submit" type="submit" name="search-icon" value="">
</form>

<div class="tasks-controls">
	<nav class="tasks-switch">
			<a href="<?= get_sort_url('all'); ?>" class="tasks-switch__item tasks-switch__item--active">Все задачи</a>
			<a href="<?= get_sort_url('today'); ?>" class="tasks-switch__item">Повестка дня</a>
			<a href="<?= get_sort_url('tomorrow'); ?>" class="tasks-switch__item">Завтра</a>
			<a href="<?= get_sort_url('expired'); ?>" class="tasks-switch__item">Просроченные</a>
	</nav>

	<label class="checkbox">
			<input class="checkbox__input visually-hidden show_completed" <?php if ($show_complete_tasks === 1):?>checked<?php endif; ?> type="checkbox" value="0">
			<span class="checkbox__text">Показывать выполненные</span>
	</label>
</div>

<table class="tasks">
	<?php foreach ($tasks as $task_number => $task_value): ?>
	<?php if (!$show_complete_tasks && $task_value['task_state'] || !in_array($task_value['task_desc'], $tasks_list)) { array_shift ($soon); continue; } ?>
	<tr class="tasks__item task <?php if ($task_value['task_state']): ?><?= 'task--completed'; ?><?php endif; ?> <?php if (array_shift ($soon)): ?><?= 'task--important'; ?><?php endif; ?>">
			<td class="task__select">
					<label class="checkbox task__checkbox">
							<input class="checkbox__input visually-hidden task__checkbox" type="checkbox" value="<?= $task_number; ?>">
							<span class="checkbox__text"><?= htmlspecialchars ($task_value['task_desc']); ?></span>
					</label>
			</td>

			<td class="task__file">
				<a class="download-link" href="<?php if ($task_value['file_link'] == null): ?>#<?php else: ?><?= $domain . $task_value['file_link'] ?><?php endif; ?>"></a>
			</td>

			<td class="task__date"><?= htmlspecialchars ($task_value['date_require']); ?></td>
	</tr>
	<?php endforeach; ?>
</table>