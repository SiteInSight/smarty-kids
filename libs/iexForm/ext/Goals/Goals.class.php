<?

/**
 * Class Goals
 *
 * Отправляет цели и события заданные заранее и зависящие от id формы
 * Шаблоны целей рознятся от системы к системе, но их составляющие идентичны
 *
 */
class Goals extends ExtensionBase {

    public $version = '1.2';

    public $priority = 800;

    protected $reserved_fields = [
        'enable_gtag'
    ];

    /**
     * Параметр переключающий отправку целей с ga на gtag
     * @var $enable_gtag необязательный
     */
    protected $enable_gtag = true;

    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);
    }

    public function beforeShow() {
        parent::beforeShow();

        $this->getField('enable_gtag')->value = $this->enable_gtag ? 'true' : 'false';
    }

    public function beforeValidate() {
        parent::beforeValidate();

        $this->getField('enable_gtag')->notsend = true;
    }
}