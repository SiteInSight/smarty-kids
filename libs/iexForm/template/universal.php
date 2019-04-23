<?if (!defined('IEXFORM')) die(); /** @var array $arData */ ?>

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

            <div class="b-pform__title">Форма со всеми типами полей</div>

            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="name-<?= $arData['uid'] ?>">Имя:</label>
                <div class="b-pform__hintwrap">
                    <input class="b-pform__input" id="name-<?= $arData['uid'] ?>" name="name" value="" type="text" data-pform-validation="required,hasRUS" data-pform-header="Имя" placeholder="Введите свое имя">
                </div>
            </div>
            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="email-<?= $arData['uid'] ?>">E-mail:</label>
                <input class="b-pform__input" id="email-<?= $arData['uid'] ?>" name="email" value="" type="text" data-pform-validation="required,isEmail" data-pform-header="E-mail">
            </div>
            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="passw-<?= $arData['uid'] ?>">Пароль:</label>
                <input class="b-pform__input" id="passw-<?= $arData['uid'] ?>" name="passw" value="" type="password" data-pform-validation="required" data-pform-header="Пароль">
            </div>
            <div class="b-pform__item">
                <label class="b-pform__label" for="text-<?= $arData['uid'] ?>">Многострочный текст:</label>
                <div class="b-pform__hintwrap">
                    <textarea class="b-pform__input" id="text-<?= $arData['uid'] ?>" name="text" data-pform-validation="hasRUS" data-pform-header="Многострочный текст"></textarea>
                </div>
            </div>
            <div class="b-pform__item">
                <label class="b-pform__label" for="htmltext-<?= $arData['uid'] ?>">Многострочный HTML-текст:</label>
                <div class="b-pform__hintwrap">
                    <textarea class="b-pform__input" id="htmltext-<?= $arData['uid'] ?>" name="htmltext" data-pform-validation="html" data-pform-header="Многострочный HTML-текст">Пример текста с <span style="color: green">HTML-разметкой,</span> которая <b style="color: red;">придет на почту</b></textarea>
                </div>
            </div>
            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <label class="b-pform__label" for="tariff-<?= $arData['uid'] ?>">Тариф:</label>
                <div class="b-pform__hintwrap">
                    <select class="b-pform__input" id="tariff-<?= $arData['uid'] ?>" name="tariff[]" data-pform-error="tariff" data-pform-header="Тариф" data-pform-validation="required">
                        <option value="disabled" selected="selected" disabled="disabled">-- выберите тариф --</option>
                        <option value="Соло 300">Соло 300</option>
                        <option value="Соло 400">Соло 400</option>
                        <option value="Соло 500">Соло 500</option>
                    </select>
                </div>
            </div>
            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <div class="b-pform__label">Образование:</div>
                <div class="b-pform__hintwrap b-pform__options" data-pform-error="edu">
                    <label class="b-pform__optlabel" for="edu-1-<?= $arData['uid'] ?>">
                        <input name="edu" id="edu-1-<?= $arData['uid'] ?>" type="radio" value="Полное среднее" data-pform-header="Образование" data-pform-validation="required">
                        Полное среднее
                    </label>
                    <label class="b-pform__optlabel" for="edu-2-<?= $arData['uid'] ?>">
                        <input name="edu" id="edu-2-<?= $arData['uid'] ?>" type="radio" value="Среднее специальное" checked="checked">
                        Среднее специальное
                    </label>
                    <label class="b-pform__optlabel" for="edu-3-<?= $arData['uid'] ?>">
                        <input name="edu" id="edu-3-<?= $arData['uid'] ?>" type="radio" value="Пример длинного текста длинного текста">
                        Пример длинного текста длинного текста
                    </label>
                    <label class="b-pform__optlabel" for="edu-4-<?= $arData['uid'] ?>">
                        <input name="edu" id="edu-4-<?= $arData['uid'] ?>" type="radio" value="Полное среднее">
                        Полное среднее
                    </label>
                    <label class="b-pform__optlabel" for="edu-5-<?= $arData['uid'] ?>">
                        <input name="edu" id="edu-5-<?= $arData['uid'] ?>" type="radio" value="Пример длинного текста длинного текста длинного текста">
                        Пример длинного текста длинного текста длинного текста
                    </label>
                    <label class="b-pform__optlabel" for="edu-6-<?= $arData['uid'] ?>">
                        <input name="edu" id="edu-6-<?= $arData['uid'] ?>" type="radio" value="Высшее">
                        Высшее
                    </label>
                </div>
            </div>
            <div class="b-pform__item">
                <div class="b-pform__star"></div>
                <div class="b-pform__label">Опыт работы:</div>
                <div class="b-pform__hintwrap b-pform__options" data-pform-error="opit">
                    <label class="b-pform__optlabel" for="opit-1-<?= $arData['uid'] ?>">
                        <input name="opit[1]" id="opit-1-<?= $arData['uid'] ?>" type="checkbox" value="Дворник" checked="checked" data-pform-header="Опыт работы" data-pform-validation="required">
                        Дворник
                    </label>
                    <label class="b-pform__optlabel" for="opit-2-<?= $arData['uid'] ?>">
                        <input name="opit[2]" id="opit-2-<?= $arData['uid'] ?>" type="checkbox" value="Бухгалтер" checked="checked">
                        Бухгалтер
                    </label>
                    <label class="b-pform__optlabel" for="opit-3-<?= $arData['uid'] ?>">
                        <input name="opit[3]" id="opit-3-<?= $arData['uid'] ?>" type="checkbox" value="Программист">
                        Программист
                    </label>
                    <label class="b-pform__optlabel" for="opit-4-<?= $arData['uid'] ?>">
                        <input name="opit[4]" id="opit-4-<?= $arData['uid'] ?>" type="checkbox" value="Пример длинного текста длинного текста">
                        Пример длинного текста длинного текста
                    </label>
                    <label class="b-pform__optlabel" for="opit-5-<?= $arData['uid'] ?>">
                        <input name="opit[5]" id="opit-5-<?= $arData['uid'] ?>" type="checkbox" value="Пример длинного текста длинного текста длинного текста">
                        Пример длинного текста длинного текста длинного текста
                    </label>
                    <label class="b-pform__optlabel" for="opit-6-<?= $arData['uid'] ?>">
                        <input name="opit[6]" id="opit-6-<?= $arData['uid'] ?>" type="checkbox" value="Менеджер">
                        Менеджер
                    </label>
                </div>
            </div>
            <div class="b-pform__item b-pform__item_file">
                <label class="b-pform__label" for="file1-<?= $arData['uid'] ?>">Файл раз:</label>
                <div class="b-pform__hintwrap" data-pform-error="file1">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">Обзор</button>
                    <input class="b-pform__filereal iexform-file-real" id="file1-<?= $arData['uid'] ?>" name="file1" type="file" data-pform-header="Файл1">
                </div>
            </div>
            <div class="b-pform__item b-pform__item_file">
                <label class="b-pform__label" for="file2-<?= $arData['uid'] ?>">Файл два:</label>
                <div class="b-pform__hintwrap" data-pform-error="file2">
                    <input class="b-pform__input b-pform__input_file iexform-file-overlay" type="text"><button class="b-pform__filebutton">Обзор</button>
                    <input class="b-pform__filereal iexform-file-real" id="file2-<?= $arData['uid'] ?>" name="file2" type="file" data-pform-header="Файл2">
                </div>
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
