<?

/**
 * Class CoMagicLead
 *
 * Данные из поля comagiclead_credentials, занесенные колбеками client.js,
 * будут использованы для создания лида в CoMagic
 */
class CoMagicLead extends ExtensionBase {

    public $version = '2.0';

    public $priority = 300;

    public $reserved_fields = [
        'credentials'
    ];

    /**
     * Имя поля "name"
     * @var string $name_field необязательное
     */
    public $name_field = 'name';

    /**
     * Имя поля "phone"
     * @var string $phone_field необязательное
     */
    public $phone_field = 'phone';

    /**
     * Имя поля "email"
     * @var string $email_field необязательное
     */
    public $email_field = 'email';

    /**
     * Список имен полей, которые в Комеджике будут добавлены в поле "Текст заявки"
     * @var string[] $additional_fields необязательное
     */
    public $additional_fields = [];

    /**
     * Блок текста, добавляемый к "Тексту заявки" в Комеджике ДО содержимого $this->$additional_fields.
     * Может содержать управляющие символы типа "\n".
     * @var string $text_before необязательное
     */
    public $text_before = '';

    /**
     * Блок текста, добавляемый к "Тексту заявки" в Комеджике ПОСЛЕ содержимого $this->$additional_fields.
     * Может содержать управляющие символы типа "\n".
     * @var string $text_after необязательное
     */
    public $text_after = '';

    /**
     * Ответ сервера CoMagic
     * @var string $result
     */
    private $result;

    public function beforeValidate() {
        parent::beforeValidate();

        $this->getField('credentials')->notsend = true;
    }

    public function onSuccess() {
        parent::onSuccess();

        $comagic = json_decode($this->getField('credentials')->value, true);

        $comagic['name'] = $this->getField($this->name_field, '') ? $this->getField($this->name_field, '')->value : '';
        $comagic['phone'] = $this->getField($this->phone_field, '') ? $this->getField($this->phone_field, '')->value : '';
        $comagic['email'] = $this->getField($this->email_field, '') ? $this->getField($this->email_field, '')->value : '';

        $comagic['text'] = $this->text_before;
        foreach ($this->additional_fields as $field_name) {
            $field = $this->getField($field_name, '');
            if ( $field !== false && $field->value ) {
                $comagic['text'] .= $field->header . ': ' . $field->value . "\n";
            }
        }
        $comagic['text'] .= $this->text_after;

        $ch = curl_init();

        /** @noinspection CurlSslServerSpoofingInspection */
        $curlopts = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'amoCRM-API-client/1.0',
            CURLOPT_URL => $comagic['consultant_server_url'] . 'api/add_offline_message/',
            CURLOPT_HTTPHEADER => array('Content-type: application/x-www-form-urlencoded; charset=UTF-8'),
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        );

        $curlopts[CURLOPT_CUSTOMREQUEST] = 'POST';
        $curlopts[CURLOPT_POSTFIELDS] = http_build_query($comagic);


        curl_setopt_array($ch, $curlopts);

        $this->result = curl_exec($ch);

        $this->core->log('CoMagicLead: Ответ сервера');
        $this->core->log($this->result);
    }
}