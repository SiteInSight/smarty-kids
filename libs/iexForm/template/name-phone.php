<?if (!defined('IEXFORM')) die(); /** @var array $arData */  ?>

<div class="b-pform__wrap">
    <div class="b-pform__inner">

        <div class="iexform-after-success">

            <div class="b-pform__confirmed">
                <i class="b-pform__confirmed-ico"></i>
                <div class="b-pform__thanks">Ваша заявка принята!</div>
                <div>Наш специалист свяжется с Вами</div>
            </div>

        </div>
        <div class="iexform-before-success active">

            <div class="b-pform__title iexform-from-position">Одношаговая форма</div>

            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="name-<?= $arData['uid'] ?>">Ваше имя:</label>
                <input class="b-pform__input" id="name-<?= $arData['uid'] ?>" name="name" value="" type="text" data-pform-validation="required,hasRUS" data-pform-header="Имя" placeholder="Введите свое имя">
            </div>
            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="phone-<?= $arData['uid'] ?>">Телефон:</label>
                <input class="b-pform__input" id="phone-<?= $arData['uid'] ?>" name="phone" value="" type="text" data-pform-validation="required" data-pform-header="Телефон" placeholder="+7 (999) 999-9999" data-pform-mask="+7 (599) 999-9999{1,10}">
            </div>

            <div class="b-pform__item">
                <label class="b-pform__optlabel b-pform__optlabel_single b-pform__optlabel_policy" data-pform-error="iexpolicy">
                    <input name="iexpolicy[1]" type="checkbox" checked="checked" value="confirm" data-pform-notsend="true">
                    Соглашаюсь с <a class="b-pform__policy iexmodal-show" data-iexmodal-width="700px" data-iexmodal-overlay="true" href="/tools/cm/iexModal/policy.htm">политикой конфиденциальности</a>
                </label>
            </div>
            <div class="b-pform__buttons">
                <input class="b-pform__bt b-pform__bt_submit" value="Отправить" type="submit">
                <input class="b-pform__bt b-pform__bt_close iexform-clear" value="Очистить" type="button">
            </div>

            <div class="iexform-common-error"></div>
            <div class="b-pform__legend"><i class="b-pform__star"></i>Поля обязательные для заполнения</div>
        </div>

    </div>
</div>
