<?if (!defined('IEXFORM')) die(); /** @var array $arData */  ?>

<div class="b-pform__wrap">
    <div class="b-pform__inner">

        <div class="iexform-after-success">

            <div class="b-pform__confirmed">
                <i class="b-pform__confirmed-ico"></i>
                <div class="b-pform__thanks">Авторизация выполнена!</div>
                <div>Перенаправляем в <a href="/personal/">Личный кабинет</a></div>
            </div>

        </div>
        <div class="iexform-before-success active">

            <div class="b-pform__title iexform-from-position">Авторизация на сайте</div>

            <div class="b-pform__item">
                <label class="b-pform__label" for="login-<?= $arData['uid'] ?>">Ваш логин:</label>
                <input class="b-pform__input" id="login-<?= $arData['uid'] ?>" name="login" value="" type="text" data-pform-validation="required">
            </div>
            <div class="b-pform__item">
                <label class="b-pform__label" for="password-<?= $arData['uid'] ?>">Пароль:</label>
                <input class="b-pform__input" id="password-<?= $arData['uid'] ?>" name="password" value="" type="password" data-pform-validation="required" >
            </div>
            <div class="b-pform__item">
                <label class="b-pform__optlabel b-pform__optlabel_single b-pform__optlabel_remember">
                    <input name="remember[1]" type="checkbox" value="Y">
                    Запомнить меня
                </label>
            </div>

            <div class="b-pform__buttons">
                <input class="b-pform__bt b-pform__bt_submit" value="Отправить" type="submit">
                <div class="b-pform__authlinks">
                    <a href="/personal/?forgot_password=yes" rel="nofollow">Забыли свой пароль?</a>
                    <br>
                    <a href="/personal/?register=yes" rel="nofollow">Зарегистрироваться</a>
                </div>
            </div>

            <div class="iexform-common-error"></div>
        </div>

    </div>
</div>
