<h2 class="content__main-heading">Добавление проекта</h2>

<form class="form"  action="add_project.php" method="post">
  <div class="form__row">
    <label class="form__label" for="project_name">Название <sup>*</sup></label>
		<?php if (array_intersect($errors, ['empty'])): ?><p class="form__message">Укажите название проекта</p><?php endif; ?>
		<?php if (array_intersect($errors, ['exist'])): ?><p class="form__message">Такой проект уже существует</p><?php endif; ?>
    <input class="form__input <?php if (!empty($errors)): ?> form__input--error <?php endif; ?>" type="text" name="name" id="project_name" value="" placeholder="Введите название проекта">
  </div>

  <div class="form__row form__row--controls">
    <input class="button" type="submit" name="" value="Добавить">
  </div>
</form>