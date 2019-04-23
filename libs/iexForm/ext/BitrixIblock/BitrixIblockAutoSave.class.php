<?php

/**
 * Class BitrixIblockAutoSave
 *
 * Сохранение в инфоблок без всяких настроек, с автоматическим сооздания типа инфоблока, самого инфоблока/инфоблоков, свойств для каждого поля формы
 */
class BitrixIblockAutoSave {

    public $iblockCode = 'iexForms';
    public $iblockId = false;

    public function __construct(){
        $this->initIBType();
        $this->initIBlock();
    }

    /**
     * Проверяет наличие типа инфоблока и создает его если он отсутсвует
     */
    private function initIBType(){
        $rsIBType = CIBlockType::GetByID($this->iblockCode);
        $arIBType = $rsIBType->GetNext();
        if ( !$arIBType ) {
            $IBType = new CIBlockType;
            $IBType->Add([
                'ID'=>$this->iblockCode,
                'SORT'=>10000,
                'LANG'=>[
                    'RU'=>['NAME'=>$this->iblockCode],
                    'EN'=>['NAME'=>$this->iblockCode]
                ]
            ]);
        }
    }

    /**
     * Достаем ID инфоблока, создавая инфоблок, если он отсутствует
     */
    private function initIBlock(){
        $rsIBlocks = CIBlock::GetList(["SORT"=>"ASC"], ['CODE'=>$this->iblockCode]);
        $arIBlock = $rsIBlocks->GetNext();

        if ( $arIBlock ) {
            $this->iblockId = $arIBlock['ID'];
        } else {
            $IBlock = new CIBlock;
            $this->iblockId = $IBlock->Add([ //SITE_ID;
                'SITE_ID' => [SITE_ID],
                'IBLOCK_TYPE_ID' => $this->iblockCode,
                'NAME' => $this->iblockCode,
                'CODE' => $this->iblockCode,
                "GROUP_ID" => ["2"=>"R"] // чтение инфоблока неавторизованными юзерами, иначе iexForm не сможет прочитать/записать (либо нужно давать модулю форм права админа Битрикса)
            ]);
        }

        return $this->iblockId;
    }

    /**
     * @param array $propParams = [
     *     'NAME' => $FieldTitle,
     *     'CODE' => $PropCode,
     *     'PROPERTY_TYPE' => $PropType, - 'S' - строка, 'F' - файл (по-умолчанию 'S')
     * ]
     * @return int|false - ID свойства или false
     */
    public function initProp($propParams = []){
        if ( !(isset($propParams['CODE'])) ) return;

        $rsProp = CIBlockProperty::GetByID($propParams['CODE'], $this->iblockId);
        $arProp = $rsProp->GetNext();

        if( $arProp ) {
            $ID = $arProp['ID'];
        } else {
            $Property = new CIBlockProperty;
            $propParams['IBLOCK_ID'] = $this->iblockId;
            $propParams['FILTRABLE'] = 'Y';
            $ID = $Property->Add($propParams);
        }

        return $ID;
    }

    /**
     * Сохранение данных в новый элемент инфоблока
     *
     * @param $elName   string  Имя элемента инфоблока
     * @param $elProps  array   Массив значений свайств элемента инфоблока
     * @return bool|int         ID элемента инфоблока или false
     */
    public function saveIblockElement($elName, $elProps){
        if ( !$elName || !$elName ) return false;

        $IBlockElement = new CIBlockElement;
        $arElement = Array(
            //"CREATED_BY"    => 1,           // 1 - создан админом, по-умолчанию - текущим пользователем
            //"MODIFIED_BY"    => 1,          // 1 - изменен админом, по-умолчанию - текущим пользователем
            "IBLOCK_ID"      => $this->iblockId,
            "IBLOCK_SECTION_ID" => false,   // положить в корень инфоблока
            "ACTIVE"         => "Y",        // "Y" - сделать элемент активным, "N" - неактивным (для премодерации отзыва/коммента)
            "DATE_ACTIVE_FROM"=>ConvertTimeStamp(false, "FULL"),    // текущая дата в кашерном для Битрикс формате
            "NAME"           => $elName,
            //"PREVIEW_TEXT_TYPE"   => 'html',    // по-умолчанию ставится 'text', несмотря на то, что в инфоблоке выставлено 'html'
            //"DETAIL_TEXT_TYPE"   => 'html',
            //"DETAIL_PICTURE" => CFile::MakeFileArray($form->fields['scan']['saved']),   // ниже есть примеры файловых свойств
            "PROPERTY_VALUES" =>$elProps
        );

        return $IBlockElement->Add($arElement);
    }

    /**
     * Сохранение формы в инфоблок
     *
     * @param {iexform.class.php}   $form   Форма с заполненными полями (экземпляр класса iexform.class.php)
     * @return bool|int                     ID элемента инфоблока или false
     */
    public function save($form){
        $propValues = [];

        foreach ($form->fields as $name=>$field){
            if (
                $field['notsend'] ||
                ( $field['sys'] && strpos($name, 'additionalfields_')!==0 )
            ) continue;

            $propCode = strtoupper(str_replace('-','_', $name));

            $this->initProp([
                'NAME' => $field['header'] ? $field['header'] : $name,
                'CODE' => $propCode,
                'PROPERTY_TYPE' => $field['type']==='file' ? 'F' : 'S',
            ]);

            $propValues[$propCode] = '';
            if ( $field['value'] ) {
                if ( $name==='iexurl' ) {
                    $serverName = str_replace('.', '\.', $_SERVER['SERVER_NAME']);
                    $propValues[$propCode] = preg_replace('#^(http|https)://'.$serverName.'(.+)#', '$2', strip_tags($field['value']));
                } elseif ( $field['type']==='file' ) {
                    $propValues[$propCode] =  CFile::MakeFileArray($field['value']);
                } else {
                    $propValues[$propCode] = $field['value'];
                }
            }
        }

        return $this->saveIblockElement($form->pformid, $propValues);
    }
}
