<?php

/**
 * FieldEntity class
 *
 * Класс сущности поля, может свободно заменить реализацию полей как массивы
 */
class FieldEntity implements ArrayAccess {

    /**
     * Имя поля на латинице, для файловых полей, после валидации примет значение "имя_файла.рсшр"
     * @var string $name
     */
    public $name;

    /**
     * Тип поля, может принимать следующие значения:
     * hidden, text, textarea, password, file, radio, checkbox, select
     * @var string $type
     */
    public $type;

    /**
     * Текстовое значение поля,
     * для radio, checkbox, select после валидации будет заполнено автоматически, значениями через запятую с пробелом
     * @var string $value
     */
    public $value;

    /**
     * Человекопонятное название поля. По-умолчанию: значение $this->name
     * Например: "Телефон"
     * @var string $header
     */
    public $header;

    /**
     * Массив строк, являющихся названиями способов валидации.
     * Например: ["isEmail", "hasRUS"]
     * @var string[] $validation
     */
    public $validation = [];

    /**
     * Текстовое значение маски, которое будет обработано на клиентской стороне.
     * Например: "+7 (599) 999-9999{1,10}"
     * @var string $mask
     */
    public $mask;

    /**
     * Флаговое поле, некоторые расширения будут ориентироваться на его значении при учете полей.
     * По-умолчанию: false
     * @var boolean $notsend
     */
    public $notsend = false;

    /**
     * Флаговое поле, некоторые ядро и некоторые расширения будут ориентироваться на его значении при учете полей.
     * По-умолчанию: false
     * @var boolean $sys
     */
    public $sys = false;

    /**
     * Список текстовых значений для полей типа radio, checkbox, select.
     * По-умолчанию: пустой массив
     * @var string[] $options
     */
    public $options = [];

    /**
     * Номер шага, которому принадлежит поле.
     * @var integer $step
     */
    public $step;

    /**
     * Номер ошибки, вызванной этим полем. 0 - отсутсвие ошибок.
     * По-умолчанию: 0
     * @var integer $error
     */
    public $error = 0;

    /**
     * Приоритет вывода поля, чем меньше, тем раньше.
     * См. рекомендации для $this->newFieldPriority в ядре
     *
     * По-умолчанию: 1000
     * @var integer $priority
     */
    public $priority = 1000;

    public function __construct($name, $value = '', $type = 'hidden') {
        $this->name = $name;
        $this->header = $name; // По-умолчанию, mailheader это name поля
        $this->value = $value;
        $this->type = $type;
    }

    public function offsetExists($name) {
        return array_key_exists($name, get_class_vars(get_class($this))) && !empty($this->{$name});
    }

    public function &offsetGet($name) {
        if(array_key_exists($name, get_class_vars(get_class($this)))){
            return $this->{$name};
        }

        return null;
    }

    public function offsetSet($name, $value) {
        if(array_key_exists($name, get_class_vars(get_class($this)))){
            $this->{$name} = $value;
        } else {
            throw new OutOfBoundsException('Field "'. $name.'" doesn\'t exist in '.get_class($this));
        }
    }

    public function offsetUnset($name) {
        $this->offsetSet($name, null);
    }

    /**
     * Возвращает истину, если поле обязательное.
     * Иначе - ложь
     *
     * @return boolean
     */
    public function isRequired(){
        return is_array($this->validation) && in_array('required', $this->validation, true);
    }

    /**
     * Магический метод, для подстановки поля в сообщения.
     *
     * @return string
     */
    public function __toString() {
        return $this->header . ': ' . $this->value;
    }
}