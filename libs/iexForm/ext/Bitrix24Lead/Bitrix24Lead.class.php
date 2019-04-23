<?php

include_once __DIR__ . '/Bitrix24API.class.php';

/**
 * Class Bitrix24Lead
 *
 * Реализация сохранения данных из полей формы в лид Битрикс24
 *
 * @see Mail
 */
class Bitrix24Lead extends ExtensionBase {

    public $version = '1.1';

    public $priority = 400;

    /**
     * Веб-хук из Bitrix24,
     * пример: `https://xxxxxx.bitrix24.ru/rest/nnn/zzzzzzzzzzzzz/`
     * @var string $webhook обязательное
     */
    protected $webhook;

    /**
     * Пары: название UF поля в Битрикс24 => имя поля в форме
     * @var array $prop_pairs необязательное, [UFNAME1 => fname1, UFNAME2 => fname2,...]
     */
    protected $prop_pairs = array();

    /**
     * От кого добавлен элемент
     * @var integer $user_id необязательно, по-умолчанию 1 (от имени Администратора)
     */
    protected $user_id = 1;

    /**
     * Имя поля, содержащее ФИО
     * @var string $name_field необязательное, по-умолчанию "name"
     */
    protected $name_field = 'name';

    /**
     * Имя поля, содержащее телефон
     * @var string $phone_field необязательное, по-умолчанию "phone"
     */
    protected $phone_field = 'phone';

    /**
     * Имя поля, содержащее E-mail
     * @var string $email_field необязательное, по-умолчанию "email"
     */
    protected $email_field = 'email';


    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);
    }

    public function onSuccess() {
        parent::onSuccess();

        if (empty($this->webhook)) {
            return true;
        }

        $api = new Bitrix24API($this->webhook);

        /* - - - - - - - - - - - */

        $iexname = $this->getField($this->name_field, '') ? $this->getField($this->name_field, '')->value : 'Без имени';
        $iexphone = $this->getField($this->phone_field, '') ? $this->getField($this->phone_field, '')->value : '';
        $iexemail = $this->getField($this->email_field, '') ? $this->getField($this->email_field, '')->value : '';
        $iexposition = $this->getField('position', 'AdditionalFields') ? $this->getField('position', 'AdditionalFields')->value : '';
        $iexurl = $this->getField('url', 'AdditionalFields') ? $this->getField('position', 'AdditionalFields')->value : '';

        // Стандартный набор полей
        $arUserFields = array(
            'SOURCE_ID' => 'WEB',
            'SOURCE_DESCRIPTION' => $iexurl,
            //'UF_CRM_LEAD_DATE' => date('d.m.Y H:i:s'),
        );

        // Кастомные поля форм
        foreach ($this->prop_pairs as $property_uf => $field_name) {
            $arUserFields[$property_uf] = $this->getField($field_name, '') ? $this->getField($field_name, '')->value : '';
        }

        // Добавление лида в Битрикс24
        $r = $api->addCrmLead($_SERVER['HTTP_HOST'] . ': ' . $iexposition, $iexname, $iexphone, $iexemail, $arUserFields, $this->user_id);
        $lead_id = 0;
        if (isset($r['result'])) {
            $lead_id = (int)$r['result'];
        }

        // Дополнительный push комментария в Битрикс24
        $ext_mail = $this->getOtherExtensionInstance('Mail');
        /**
         * @var Mail $ext_mail
         */
        if ($lead_id > 0 && $ext_mail !== false && method_exists($ext_mail, 'getMailHtml')) {
            $comment = strip_tags($ext_mail->getMailHtml());
            if ($comment) {
                $api->addCrmLivefeedmessage('Текст письма', $comment, $lead_id, 1);
            }
        }


    }

}