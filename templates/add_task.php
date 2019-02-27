<h2 class="content__main-heading">Добавление задачи</h2>

<form class="form"  action="add_task.php" method="post" enctype="multipart/form-data">
  <div class="form__row">
    <label class="form__label" for="name">Название <sup>*</sup></label>
    <?php if (array_intersect($errors, ['empty'])): ?><p class="form__message">Укажите название задачи</p><?php endif; ?>
    <input class="form__input <?php if (array_intersect($errors, ['empty'])): ?> <?= 'form__input--error' ?> <?php endif; ?>" type="text" name="name" id="name" value="" placeholder="Введите название">
  </div>

  <div class="form__row">
    <label class="form__label" for="project">Проект</label>
    <?php if (array_intersect($errors, ['exist'])): ?><p class="form__message">Проект не найден</p><?php endif; ?>
    <select class="form__input form__input--select <?php if (array_intersect($errors, ['exist'])): ?> <?= 'form__input--error' ?> <?php endif; ?>" name="project" id="project">
    <?php foreach ($category_list as $category_value): ?>
    <?= '<option value="' . $category_value['category_id'] . '">' . $category_value['category_name'] . '</option>' ?>
    <?php endforeach; ?>
    </select>
  </div>

  <div class="form__row">
    <label class="form__label" for="date">Дата выполнения</label>
    <?php if (array_intersect($errors, ['date'])): ?><p class="form__message">Некорректная дата</p><?php endif; ?>
    <input class="form__input form__input--date <?php if (array_intersect($errors, ['exist'])): ?> <?= 'form__input--error' ?> <?php endif; ?>" type="date" name="date" id="date" value="" placeholder="Введите дату в формате ДД.ММ.ГГГГ">
  </div>

  <div class="form__row">
    <label class="form__label" for="preview">Файл</label>

    <div class="form__input-file">
      <input class="visually-hidden" type="file" name="preview" id="preview" value="">

      <label class="button button--transparent" for="preview">
        <span>Выберите файл</span>
      </label>
    </div>
  </div>

  <div class="form__row form__row--controls">
    <input class="button" type="submit" name="" value="Добавить">
  </div>
</form>