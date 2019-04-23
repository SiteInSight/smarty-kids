<?php
/**
 * Class BitrixAuth
 *
 * Авторизация в Bitrix с помощью формы с полями login и password.
 * В конфиге нужно не забыть отключить расширения Mail, AdditionalFields и любые другие, ненужные для авторизации.
 * Подходящий шаблон формы расположен в файле auth.php.
 *
 */
class BitrixAuth extends ExtensionBase {

    public $version = '1.0';

    public $priority = 450;

    /**
     * Урл страниы, на которую нужно редиректить после авторизации.
     *
     * @var string $redirectUrl Необязательное, по-умолчанию редирект будет на текущую страницу (перезагрузка)
     */
    public $redirectUrl = '';

    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);

        $this->reserved_fields[] = 'redirect-url';
    }

    public function beforeShow() {
        parent::beforeShow();

        $fieldUrl = $this->getField('redirect-url');
        $fieldUrl->value = $this->redirectUrl;
    }

    public function afterValidate($isValid) {
        parent::afterValidate($isValid);

        if ( $isValid ) return true;
        require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
        if ( !CModule::IncludeModule('iblock') ) return true;

        global $USER;
        $isAuthorized = $USER->Login(
            $this->getField('login', '')->value,
            $this->getField('password', '')->value,
            $this->getField('remember', '')->value
        );
        if ( $isAuthorized !== true ) {
            $this->core->state['isValid'] = false;
            $this->core->state['errCode'] = $this->core::ERR_COMMON_AUTH;
        }
    }

}