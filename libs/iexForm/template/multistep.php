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

            <div class="b-pform__title">Многошаговая форма с подсказками для полей</div>

            <div class="b-pform__step b-pform__step_curr" data-pform-step="1">
                <div class="b-pform__steptitle">ШАГ 1</div>
                <div class="b-pform__item">
                    <div class="b-pform__star"></div>
                    <label class="b-pform__label" for="name-<?= $arData['uid'] ?>">Ваше имя:</label>
                    <div class="b-pform__hintwrap">
                        <div class="b-pform__hint-ico iexform-hint" title="Как к Вам обращаться?"><i class="iexform-svg" data-id="question"></i></div>
                        <input class="b-pform__input" id="name-<?= $arData['uid'] ?>" name="name" value="" type="text" data-pform-validation="required,hasRUS" data-pform-header="Имя" placeholder="Введите свое имя">
                    </div>
                </div>
                <div class="b-pform__item">
                    <label class="b-pform__label" for="text-<?= $arData['uid'] ?>">Текст:</label>
                    <div class="b-pform__hintwrap">
                        <div class="b-pform__hint-ico iexform-hint" title="С чувством, с толком, с расстановкой, подробно и доходчиво )))"><i class="iexform-svg" data-id="question"></i></div>
                        <textarea class="b-pform__input" id="text-<?= $arData['uid'] ?>" name="text" data-pform-header="Текст"></textarea>
                    </div>
                </div>
            </div>

            <div class="b-pform__step" data-pform-step="2">
                <div class="b-pform__steptitle">ШАГ 2</div>
                <div class="b-pform__item">
                    <div class="b-pform__star"></div>
                    <div class="b-pform__label">Опыт работы:</div>
                    <div class="b-pform__hintwrap b-pform__options" data-pform-error="opit">
                        <div class="b-pform__hint-ico iexform-hint" title="Как к Вам обращаться?"><i class="iexform-svg" data-id="question"></i></div>
                        <label class="b-pform__optlabel">
                            <input name="opit[1]" type="checkbox" value="Дворник" checked="checked" data-pform-header="Опыт работы" data-pform-validation="required">
                            Дворник
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="opit[2]" type="checkbox" value="Бухгалтер" checked="checked">
                            Бухгалтер
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="opit[3]" type="checkbox" value="Программист">
                            Программист
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="opit[4]" type="checkbox" value="Пример длинного текста длинного текста">
                            Пример длинного текста длинного текста
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="opit[5]" type="checkbox" value="Пример длинного текста длинного текста длинного текста">
                            Пример длинного текста длинного текста длинного текста
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="opit[6]" type="checkbox" value="Менеджер">
                            Менеджер
                        </label>
                    </div>
                </div>
                <div class="b-pform__item b-pform__item_file">
                    <label class="b-pform__label" for="file1-<?= $arData['uid'] ?>">Файл раз:</label>
                    <div class="b-pform__hintwrap" data-pform-error="file1">
                        <div class="b-pform__hint-ico iexform-hint" title="Подсказка"><i class="iexform-svg" data-id="question"></i></div>
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
            </div>

            <div class="b-pform__step" data-pform-step="3">
                <div class="b-pform__steptitle">ШАГ 3</div>
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
                        <div class="b-pform__hint-ico iexform-hint" title="Как к Вам обращаться?"><i class="iexform-svg" data-id="question"></i></div>
                        <label class="b-pform__optlabel">
                            <input name="edu" type="radio" value="Полное среднее" data-pform-header="Образование" data-pform-validation="required">
                            Полное среднее
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="edu" type="radio" value="Среднее специальное" checked="checked">
                            Среднее специальное
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="edu" type="radio" value="Пример длинного текста длинного текста">
                            Пример длинного текста длинного текста
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="edu" type="radio" value="Полное среднее">
                            Полное среднее
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="edu" type="radio" value="Пример длинного текста длинного текста длинного текста">
                            Пример длинного текста длинного текста длинного текста
                        </label>
                        <label class="b-pform__optlabel">
                            <input name="edu" type="radio" value="Высшее">
                            Высшее
                        </label>
                    </div>
                </div>

                <div class="b-pform__item">
                    <label class="b-pform__optlabel b-pform__optlabel_single b-pform__optlabel_policy" data-pform-error="iexpolicy">
                        <input name="iexpolicy[1]" type="checkbox" checked="checked" value="confirm" data-pform-notsend="true">
                        Соглашаюсь с <a class="b-pform__policy iexmodal-show" data-iexmodal-width="700px" data-iexmodal-overlay="true" href="/tools/cm/iexModal/policy.htm">политикой конфиденциальности</a>
                    </label>
                </div>
            </div>

            <div class="b-pform__buttons">
                <div class="b-pform__btcol">
                    <button class="b-pform__bt b-pform__bt_prev iexform-step-prev"><i class="iexform-svg" data-id="angle-left"></i></button>
                </div>
                <div class="b-pform__btcol">
                    <div class="b-pform__stepbulls iexform-step-bulls"></div>
                </div>
                <div class="b-pform__btcol">
                    <button class="b-pform__bt b-pform__bt_next" type="submit">Далее <i class="iexform-svg" data-id="angle-right"></i></button>
                </div>
            </div>

            <div class="iexform-common-error"></div>
            <div class="b-pform__legend"><i class="b-pform__star"></i>Поля обязательные для заполнения</div>
        </div>

    </div>
</div>
