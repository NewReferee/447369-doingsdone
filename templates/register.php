 <h2 class="content__main-heading">Регистрация аккаунта</h2>

<form class="form" action="register.php" method="post">
  <div class="form__register">
    <div class="form__row">
      <label class="form__label" for="email">E-mail <sup>*</sup></label>
      <?php if (array_intersect($errors, ['invalid-email'])): ?><p class="form__message">E-mail введён некорректно</p><?php endif; ?>
      <?php if (array_intersect($errors, ['exist-email'])): ?><p class="form__message">E-mail уже существует</p><?php endif; ?>
      <input class="form__input" type="text" name="email" id="email" value="" placeholder="Введите e-mail">

    </div>

    <div class="form__row">
      <label class="form__label" for="password">Пароль <sup>*</sup></label>
      <?php if (array_intersect($errors, ['empty-password'])): ?><p class="form__message">Укажите пароль</p><?php endif; ?>
      <input class="form__input" type="password" name="password" id="password" value="" placeholder="Введите пароль">
    </div>

    <div class="form__row">
      <label class="form__label" for="name">Имя <sup>*</sup></label>
      <?php if (array_intersect($errors, ['empty-name'])): ?><p class="form__message">Укажите имя</p><?php endif; ?>
      <?php if (array_intersect($errors, ['exist-name'])): ?><p class="form__message">Имя уже существует</p><?php endif; ?>
      <input class="form__input" type="text" name="name" id="name" value="" placeholder="Введите имя">
    </div>
  </div>

  <div class="form__row form__row--controls">
    <?php if (!empty($errors)): ?><p class="error-message">Пожалуйста, исправьте ошибки в форме</p><?php endif; ?>

    <input class="button" type="submit" name="" value="Зарегистрироваться">
  </div>
</form>