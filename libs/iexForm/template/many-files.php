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

            <div class="b-pform__title">Форма с множеством файловых полей</div>

            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="name-<?= $arData['uid'] ?>">Ваше имя:</label>
                <input class="b-pform__input" id="name-<?= $arData['uid'] ?>" name="name" value="" type="text" data-pform-validation="required,hasRUS" data-pform-header="Имя" placeholder="Введите свое имя">
            </div>

            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="text1-<?= $arData['uid'] ?>">Набор текстовых полей:</label>
                <input class="b-pform__input" id="text1-<?= $arData['uid'] ?>" name="text1" value="" type="text" data-pform-header="Одно поле из набора" data-pform-validation="hasRUS">
                <input class="b-pform__input iexform-plus-item" id="text2-<?= $arData['uid'] ?>" name="text2" value="" type="text" data-pform-header="Одно поле из набора" data-pform-validation="hasRUS" style="display:none">
                <input class="b-pform__input iexform-plus-item" id="text3-<?= $arData['uid'] ?>" name="text3" value="" type="text" data-pform-header="Одно поле из набора" data-pform-validation="hasRUS" style="display:none">
                <input class="b-pform__input iexform-plus-item" id="text4-<?= $arData['uid'] ?>" name="text4" value="" type="text" data-pform-header="Одно поле из набора" data-pform-validation="hasRUS" style="display:none">
                <a class="b-pform__plusbt iexform-plus-button" href="#">еще поле +</a>
            </div>

            <div class="b-pform__item b-pform__item_file">
                <label class="b-pform__label" for="file-<?= $arData['uid'] ?>">Одно вложение:</label>
                <div class="b-pform__hintwrap" data-pform-error="file">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">выбрать</button>
                    <input class="b-pform__filereal iexform-file-real" id="file-<?= $arData['uid'] ?>" name="file" type="file" data-pform-header="Одиночный файл">
                </div>
            </div>

            <div class="b-pform__item">
                <label class="b-pform__label" for="filebt-<?= $arData['uid'] ?>">Файловое поле кнопкой:</label>
                <div class="b-pform__bt b-pform__bt_file" data-pform-error="filebt">
                    <span class="iexform-file-overlay" data-pform-file-chosen="Вложение выбрано">Выбрать вложение</span>
                    <input class="b-pform__filereal iexform-file-real" type="file" name="filebt" data-pform-header="Файловое поле кнопкой">
                </div>
            </div>

            <div class="b-pform__item b-pform__item_file">
                <label class="b-pform__label" for="file1-<?= $arData['uid'] ?>">Много вложений:</label>
                <div class="b-pform__hintwrap" data-pform-error="file1">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">выбрать</button>
                    <input class="b-pform__filereal iexform-file-real" id="file1-<?= $arData['uid'] ?>" name="file1" type="file" data-pform-header="Вложение">
                </div>
                <div class="b-pform__hintwrap iexform-plus-item" data-pform-error="file2" style="display: none">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">выбрать</button>
                    <input class="b-pform__filereal iexform-file-real" id="file2-<?= $arData['uid'] ?>" name="file2" type="file" data-pform-header="Вложение">
                </div>
                <div class="b-pform__hintwrap iexform-plus-item" data-pform-error="file3" style="display: none">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">выбрать</button>
                    <input class="b-pform__filereal iexform-file-real" id="file3-<?= $arData['uid'] ?>" name="file3" type="file" data-pform-header="Вложение">
                </div>
                <div class="b-pform__hintwrap iexform-plus-item" data-pform-error="file4" style="display: none">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">выбрать</button>
                    <input class="b-pform__filereal iexform-file-real" id="file4-<?= $arData['uid'] ?>" name="file4" type="file" data-pform-header="Вложение">
                </div>
                <div class="b-pform__hintwrap iexform-plus-item" data-pform-error="file5" style="display: none">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">выбрать</button>
                    <input class="b-pform__filereal iexform-file-real" id="file5-<?= $arData['uid'] ?>" name="file5" type="file" data-pform-header="Вложение">
                </div>
                <a class="b-pform__plusbt iexform-plus-button" href="#">еще вложение +</a>
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
