<?

class Roistat extends ExtensionBase {

    public $version = '1.0';

    public $priority = 850;

    /**
     * Roistat integration key
     * @var string $key обязательный
     */
    protected $key;

    /**
     * Название сделки в Roistat
     * @var string $title необязательное
     */
    protected $title = '';

    /**
     * Имя поля "name"
     * @var string $name_field необязательное
     */
    protected $name_field = 'name';

    /**
     * Имя поля "phone"
     * @var string $phone_field необязательное
     */
    protected $phone_field = 'phone';

    /**
     * Имя поля "email"
     * @var string $email_field необязательное
     */
    protected $email_field = 'email';

    /**
     * Список имен полей, которые будут добавлены в комментарий
     * @var string[] $additional_fields необязательное
     */
    protected $additional_fields = [];

    /**
     * Ответ сервера Roistat
     * @var string $result
     */
    private $result;

    public function onSuccess() {
        parent::onSuccess();

        $comment = '';
        foreach ($this->additional_fields as $field_name) {
            $field = $this->getField($field_name);
            if (empty($field->value)) {
                continue;
            }

            $comment .= $field . "\n";
        }

        $roistatData = array(
            'roistat' => isset($_COOKIE['roistat_visit']) ? $_COOKIE['roistat_visit'] : null,
            'key'     => $this->key, // Ключ для интеграции с CRM, указывается в настройках интеграции с CRM.
            'title'   => $this->title, // Название сделки
            'comment' => $comment, // Комментарий к сделке
            'name'    => $this->getField($this->name_field, '') ? $this->getField($this->name_field, '')->value : '', // Имя клиента
            'email'   => $this->getField($this->email_field, '') ? $this->getField($this->email_field, '')->value : '', // Email клиента
            'phone'   => $this->getField($this->phone_field, '') ? $this->getField($this->phone_field, '')->value : '', // Номер телефона клиента
            'is_need_callback' => '0', // После создания в Roistat заявки, Roistat инициирует обратный звонок на номер клиента, если значение параметра рано 1 и в Ловце лидов включен индикатор обратного звонка.
            'callback_phone' => '', // Переопределяет номер, указанный в настройках обратного звонка.
            'sync'    => '0', // 
            'is_need_check_order_in_processing' => '1', // Включение проверки заявок на дубли
            'is_need_check_order_in_processing_append' => '1', // Если создана дублирующая заявка, в нее будет добавлен комментарий об этом
            'fields'  => array(),
        );
        
        
        $ch = curl_init();

        /** @noinspection CurlSslServerSpoofingInspection */
        $curlopts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'iexCRM-API-client/1.0',
            CURLOPT_URL => "https://cloud.roistat.com/api/proxy/1.0/leads/add",
            CURLOPT_HTTPHEADER => array('Content-type: application/x-www-form-urlencoded; charset=UTF-8'),
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        );

        $curlopts[CURLOPT_CUSTOMREQUEST] = 'POST';
        $curlopts[CURLOPT_POSTFIELDS] = http_build_query($roistatData);


        curl_setopt_array($ch, $curlopts);

        $this->result = curl_exec($ch);

    }
}