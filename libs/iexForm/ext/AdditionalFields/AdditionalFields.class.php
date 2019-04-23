<?

/**
 * Class AdditionalFields
 *
 * Создает и генерирует/получает значения для дополнительных полей
 * используемых другими расширениями
 *
 */
class AdditionalFields extends ExtensionBase {

    public $version = '1.1';

    public $priority = 150;

    /**
     * Добавляет в список полей до валидации автозаполняемое поле IP
     * @var $ip необязательный, по-умолчанию - ложь
     */
    protected $ip = false;

    /**
     * Добавляет в список полей автозаполняемое поле url
     * @var $url необязательный, по-умолчанию - истина
     */
    protected $url = true;

    /**
     * Добавляет в список полей автозаполняемое поле position
     * @var $url необязательный, по-умолчанию - истина
     */
    protected $position = true;

    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);

        if($this->ip) {
            $this->reserved_fields[] = 'ip';
        }

        if($this->url){
            $this->reserved_fields[] = 'url';
        }

        if($this->position){
            $this->reserved_fields[] = 'position';
        }
    }

    public function beforeValidate() {
        parent::beforeValidate();

        if($this->ip) {
            $field = $this->getField('ip');
            $field->header = 'IP';
            $field->value = $_SERVER['REMOTE_ADDR'];
            $field->notsend = 'false';
            $field->priority = 2000;
        }

        if($this->url) {
            $field = $this->getField('url');
            $field->header = 'Страница сайта';
            $field->notsend = false;
            $field->priority = 200;
        }

        if($this->position) {
            $field = $this->getField('position');
            $field->header = 'Заполнена форма';
            $field->notsend = false;
            $field->priority = 100;
        }
    }
}