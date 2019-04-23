<?php

include_once 'BitrixAutoSave.class.php';

/**
 * Class BitrixIblock
 *
 * Реализация сохранения данных из полей формы в инфоблок
 *
 */
class BitrixIblock extends ExtensionBase {

    public $version = '3.0';

    public $priority = 500;

    /**
     * Включить сохранение в инфоблок без всяких настроек, с автоматическим сооздания типа инфоблока, самого инфоблока/инфоблоков, свойств для каждого поля формы
     * @var boolean $iblock_id необязательный
     */
    protected $auto_save = true;

    /**
     * ID инфоблока
     * @var integer $iblock_id обязательный
     */
    protected $iblock_id;

    /**
     * Тип инфоблока
     * @var string $iblock_type обязательный
     */
    protected $iblock_type;

    /**
     * Активность добавляемого элемента инфоблока
     * @var boolean $element_active необязательный
     */
    protected $element_active = false;

    /**
     * Поля инфоблока и соответствующие им поля формы, либо просто любые другие скалярные значения
     * @var array $iblock_fields обязательный [PNAME1 => fname1, PNAME2 => fname2,...]
     */
    protected $iblock_fields = array();

    /**
     * Свойства инфоблока и соответствующие им поля формы, либо просто любые другие скалярные значения
     * @var array $iblock_props обязательный [PNAME1 => fname1, PNAME2 => fname2,...]
     */
    protected $iblock_props = array();

    /**
     * От кого добавлен элемент
     * @var integer $user_id необязательный
     */
    protected $user_id = 1;

    /**
     * Дописывать ли комментарий к письму, по-умолчанию true
     * @var boolean $add_comment необязательный Если true, то комментарий будет отдаваться методом Bitrix::getComment()
     * @see Bitrix::getComment()
     */
    protected $add_comment = true;

    /**
     * Содержит комментарий, доступный методом Bitrix::getComment() для сторонних плагинов
     * @var string $comment
     * @see Bitrix::getComment()
     */
    private $comment;

    public function onSuccess() {
        parent::onSuccess();

        if( class_exists('CModule') === false ){
            require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
        }

        if ( !CModule::IncludeModule('iblock') ) {
            return true;
        }

        if ( $this->auto_save ) {
            $autoSave = new BitrixAutoSave();
            $autoSave->save($this->core);
            return true;
        }

        if (
            $this->iblock_id === null ||
            $this->iblock_type === null
        ) {
            return true;
        }

        $arElement = Array(
            "CREATED_BY" => $this->user_id,
            "MODIFIED_BY" => $this->user_id,
            "IBLOCK_ID" => $this->iblock_id,
            "IBLOCK_SECTION_ID" => false,
            "ACTIVE" => $this->element_active ? "Y" : 'N',
            "DATE_ACTIVE_FROM" => ConvertTimeStamp(false, "FULL"),
            "NAME" => $this->getFormUID(),
        );

        $arFields = array();
        foreach ($this->iblock_fields as $ib_code => $value) {
            $form_field = $this->getField($value, '');
            if ( $form_field===false ) {
                $arFields[$ib_code] = $value;
            } else {
                $arFields[$ib_code] = $form_field->value;
            }
        }

        $arElement = array_merge($arElement, $arFields);

        $arProps = array();
        foreach ($this->iblock_props as $ib_code => $value) {
            $form_field = $this->getField($value, '');
            if ( $form_field===false ) {
                $arProps[$ib_code] = $value;
            } else {
                $arProps[$ib_code] = $form_field->value;
            }
        }
        $arElement["PROPERTY_VALUES"] = $arProps;

        $IBlockElement = new CIBlockElement;
        $addedID = $IBlockElement->Add($arElement);

        if ($addedID) {
            $this->comment =
                '<p>' .
                'Просмотр в админке: ' . ($_SERVER["HTTPS"] ? 'https' : 'http') . '://' . $_SERVER["SERVER_NAME"] . '/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=' . $this->iblock_id . '&type=' . $this->iblock_type . '&ID=' . $addedID . '&lang=ru&find_section_section=0&WF=Y' .
                '</p>';
        } else {
            $this->comment = '<p>Заявка не сохранена в инфоблок из-за ошибки:' . $IBlockElement->LAST_ERROR . '</p>';
        }
    }

    /**
     * Метод возвращает комментарий,
     * доступный после исполнения метода Bitrix::afterValidate(), если Bitrix::$add_comment === true
     *
     * @see Bitrix::$add_comment
     * @see Bitrix::$comment
     * @return string
     */
    public function getComment(): string {
        if( $this->add_comment === true && !empty($this->comment) ) {
            return $this->comment;
        }
        return '';
    }

}