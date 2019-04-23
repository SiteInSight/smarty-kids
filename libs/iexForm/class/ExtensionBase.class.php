<?php

/**
 * Class ExtensionBase
 *
 * Реализует базовый функционал каждого расширения
 */
abstract class ExtensionBase {

    /**
     * Текущая версия расширения,
     * служит для контроля версий расширений на проектах
     *
     * @var string $version
     */
    public $version = '1.2';

    /**
     * Приоритет исполнения расширения
     * Чем меньше, тем раньше
     *
     * @var string $priority По-умолчанию максимальный приоритет (исполняется последним)
     */
    public $priority = 99999999;

    /**
     * Ссылка на объект текущей формы,
     * позволяет вызывать методы ядра
     *
     * @var IexForm $core
     */
    protected $core;

    /**
     * Определяет список полей расширения, для которых необходимо отключить валидацию
     * префикс будет подставлен автоматически
     *
     * @var array $reserved_fields
     */
    protected $reserved_fields = [];

    /**
     * Выполняется при каждой инициализации расширения
     *
     * @param IexForm $core Ссылка на this основного модуля
     * @param array $params Массив с параметрами расширения из конфиг-файла
     *
     * @return void
     */
    public function __construct($core, array $params = array()) {
        $this->core = &$core;

        // Переданные параметры становятся полями экземляра
        foreach ($params as $key => $value) {
            if ( !isset($this->core->callbacks[$key] ) ) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Выполняется перед парсингом шаблона
     *
     * @return boolean|void Если возвращает false, то выполнение прекращается
     */
    public function beforeParseTemplate() {

    }

    /**
     * Выполняется перед отправкой формы клиенту
     *
     * @return boolean|void Если возвращает false, то выполнение прекращается
     */
    public function beforeShow() {
        foreach ($this->reserved_fields as $field_name) {
            $prefix = $this->getPreparedPrefix('__EXTENSION__');
            if ($field = $this->getField($prefix . $field_name, '')) {
                $field->sys = true;
            } else {
                $this->core->addSysField($prefix . $field_name);
            }
        }
    }

    /**
     * Выполняется до проверки полей
     * по-умолчанию делает перечисленные в $this->reserved_fields поля системными
     * по-умолчанию заполняет эти поля из $_POST
     *
     * @return boolean|void Если возвращает false, то выполнение прекращается
     */
    public function beforeValidate() {
        foreach ($this->reserved_fields as $field_name) {
            $prefix = $this->getPreparedPrefix('__EXTENSION__');
            if ($field = $this->getField($prefix . $field_name, '')) {
                $field->sys = true;
            } else {
                $this->core->addSysField($prefix . $field_name);
                $field = $this->getField($prefix . $field_name, '');
            }

            if(isset($_POST[$prefix . $field_name])) {
                $field->value = trim($_POST[$prefix . $field_name]);
            }
        }
    }

    /**
     * Выполняется после проверки полей и может переопределять результаты
     * валидации, меняя $this->core->state['errCode'] и $this->core->state['isValid']
     *
     * @param boolean $isValid Содержит true если валидация прошла успешна
     * @return boolean|void Если возвращает false, то выполнение прекращается
     */
    public function afterValidate($isValid) {

    }

    /**
     * Выполняется после успешного заполнения всех полей всех шагов формы
     *
     * @return void
     */
    public function onSuccess() {

    }

    /**
     * Конец выполнения скрипта
     *
     * @return void
     */
    public function onFinish() {

    }

    /**
     * Возвращает список объектов полей, либо пустой массив
     *
     * @return FieldEntity[]
     */
    public function getFields() {
        $fields = array_values($this->core->fields);
        // Сортировка по приоритету (по возрастанию)
        usort($fields, function (FieldEntity $a, FieldEntity $b) {
            return
                ( $a->priority === $b->priority ) ? 0 : ( ($a->priority > $b->priority) ? 1 : -1 );
        });
        return $fields;
    }

    /**
     * Возвращает объект поля, если оно существует
     *
     * @param string $name Имя поля
     * @param string $prefix По-умолчанию __EXTENSION__ (подменяется на имя класса-расширения)
     * @return FieldEntity|bool Объект поля или ложь, если оно не существует
     */
    public function getField($name, $prefix = '__EXTENSION__'){
        $name = $this->getPreparedPrefix($prefix) . $name;
        if (!isset($this->core->fields[$name])) {
            return false;
        }

        return $this->core->fields[$name];
    }

    /**
     * Возвращает подготовленную строку-префикс:
     * - если передана *пустая* строка, возвращается пустая строка
     * - если передана строка *\_\_EXTENSION\_\_*, входные данные подменяются на имя класса-расширения
     * - в остальных случаях строка приводится к нижнему регистру, символы *" -.,"* заменяются на *"\_"*,
     * в конец возвращаемой строки подставляется *"\_"*
     *
     * @param string $prefix Сырая строка
     * @return string Подготовленная строка
     */
    public function getPreparedPrefix($prefix): string {

        if ($prefix === '') {
            return $prefix;
        }

        if ($prefix === '__EXTENSION__') {
            $prefix = get_class($this);
        }

        $prefix = mb_strtolower($prefix, 'UTF-8');
        $prefix = str_replace([' ', '-', '.', ','], '_', $prefix);
        return $prefix . '_';
    }

    /**
     * Возвращает экзмепляр расширения
     *
     * @param string $name Имя расширения
     * @return ExtensionBase|boolean Объект расширения, если оно не инициализировано - ложь
     */
    protected function getOtherExtensionInstance($name){
        if ( !isset($this->core->exts[$name]) ){
            return false;
        }
        return $this->core->exts[$name];
    }

    /**
     * Аналог предыдущего метода с более коротким названием
     *
     * @param string $name Имя расширения
     * @return ExtensionBase|boolean Объект расширения, если оно не инициализировано - ложь
     */
    protected function ext($name){
        if ( !isset($this->core->exts[$name]) ){
            return false;
        }
        return $this->core->exts[$name];
    }

    protected function getFormUID(): string{
        return $this->core->uid;
    }

}