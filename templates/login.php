<h2 class="content__main-heading">Вход на сайт</h2>

<form class="form" action="login.php" method="post">
  <div class="form__row">
    <label class="form__label" for="email">E-mail <sup>*</sup></label>
    <?php if (array_intersect($errors, ['invalid-email'])): ?><p class="form__message">E-mail введён некорректно</p><?php endif; ?>
    <input class="form__input <?php if (array_intersect($errors, ['invalid-email'])): ?><?= 'form__input--error' ?><?php endif; ?>" type="text" name="email" id="email" value="" placeholder="Введите e-mail">
  </div>

  <div class="form__row">
    <label class="form__label" for="password">Пароль <sup>*</sup></label>
    <?php if (array_intersect($errors, ['empty-password'])): ?><p class="form__message">Укажите пароль</p><?php endif; ?>
    <?php if (array_intersect($errors, ['invalid-password'])): ?><p class="form__message">Неверный пароль</p><?php endif; ?>
    <input class="form__input <?php if (array_intersect($errors, ['empty-password', 'invalid-password'])): ?><?= 'form__input--error' ?><?php endif; ?>" type="password" name="password" id="password" value="" placeholder="Введите пароль">
  </div>

  <div class="form__row form__row--controls">
    <input class="button" type="submit" name="" value="Войти">
  </div>
</form>
