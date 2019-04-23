<?

/**
 * Class Utm
 *
 * Get-параметры обрабатываем НЕ переданные данному скрипту (из $_GET),
 * а переданные из браузера вместе с данными формы, в поле utm_getstring
 */
class Utm extends ExtensionBase {

    public $version = '1.3';

    public $priority = 200;

    protected $reserved_fields = [
        'getstring'
    ];

    /**
     * Белый список рекламных get параметров для фильтрации
     * Если пуст - не фильтруется
     * @var $getParamsFilter необязательный, по-умолчанию - []
     */
    protected $getParamsFilter = [];

    /**
     * Сопоставление get параметра, его человекочитаемому аналогу
     * @var $getParamsTitles необязательный, по-умолчанию - стандартные параметры
     */
    protected $getParamsTitles = [
        'utm_source' => 'Источник',
        'utm_medium' => 'Канал',
        'utm_campaign' => 'Название',
        'utm_term' => 'Ключевое слово',
        'utm_content' => 'Содержание кампании',
        'type' => 'Тип площадки',
        'source' => 'Название площадки РСЯ',
        'added' => 'Инициирован ли этот показ',
        'block' => 'Тип блока',
        'pos' => 'Позиция в блоке',
        'key' => 'Ключевая фраза',
        'campaign' => 'Номер (ID) кампании',
        'ad' => 'Номер (ID) объявления',
        'phrase' => 'Номер (ID) ключевой фразы',
        'network' => 'Тип площадки',
        'placement' => 'Адрес площадки',
        'keyword' => 'Ключевое слово'
    ];

    private $getParamsAll = [];
    private $getParamsFiltered = [];

    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);

        if (!is_array($this->getParamsFilter)) {
            $this->getParamsFilter = [];
        }
        if (!is_array($this->getParamsTitles)) {
            $this->getParamsTitles = [];
        }
    }

    public function afterValidate($isValid) {
        parent::afterValidate($isValid);

        $getstring = $this->getField('getstring')->value;
        parse_str($getstring, $this->getParamsAll);

        $this->getParamsFiltered = $this->getParamsAll;
        if (count($this->getParamsFilter)) {
            foreach ($this->getParamsFiltered as $param => $value) {
                if (!in_array($param, $this->getParamsFilter, true)) {
                    unset($this->getParamsFiltered[$param]);
                }
            }
        }
    }

    /**
     * Возвращает массив пар name => value
     * Отфильтрованных, если параметр getParamsFilter был не пуст
     *
     * @return array $this->getParamsFiltered
     */
    public function getGetParamsFiltered(): array {
        return $this->getParamsFiltered;
    }

    /**
     * @param string $param Имя $_GET параметра
     * @return string Переведенное имя, либо значение входного параметра
     */
    public function getGetParamsTitles($param): string {
        if(isset($this->getParamsTitles[$param])){
            return $this->getParamsTitles[$param];
        }

        return $param;
    }
}