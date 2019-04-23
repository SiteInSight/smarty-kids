<?

/**
 * Class CheckProduction
 *
 * Проверяет удовлетворяет ли состояние форм production сайт,
 * иначе создает репорт в Telegram при каждом onSuccess
 */
class CheckProduction extends ExtensionBase {

    public $version = '1.0';

    public $priority = 250;

    /**
     * Токен полученный от "@BotFather"
     * @var string $bot_token необязательный
     */
    protected $bot_token = '604114644:AAGa2EAyK-OGl0ko1GKsppczszXbgk9eQQM';

    /**
     * Числовой id чата в Telegram, к которому есть доступ у бота
     * @var integer $chat_id необязательный
     */
    protected $chat_id = -1001347383478; // IexForm Report Channel

    /**
     * Строка вида: "socks5://bob:marley@localhost:12345", по-умолчанию: прокси IEX
     * Если пусто - прокси не используется
     * @var string $proxy_data необязательный
     */
    protected $proxy_data = 'socks5://iexproxy:iexproxypass@vpn2.alexxwiz.ru:1080';

    /**
     * Ответ сервера Telegram
     * @var string $result
     */
    private $result;


    public function onSuccess() {
        parent::onSuccess();

        if(preg_match('/\.bex\.su$/', $_SERVER['HTTP_HOST']) === 1) {
            return true;
        }


        $mail_ext = $this->getOtherExtensionInstance('Mail');
        if($mail_ext === false) {
            return true;
        }

        /**
         * @var Mail $mail_ext
         */

        $emails = $mail_ext->getToEmail();
        if(!$emails) {
            return true;
        }

        $emails_valid = false;
        $devided_emails = explode(',', $emails);
        foreach ($devided_emails as $email) {
            $email = trim($email);
            if(preg_match('/@iex.su$/', $email) === 0) {
                $emails_valid = true;
                break;
            }
        }

        if(!$emails_valid) {
            $url = 'https://api.telegram.org/bot' . $this->bot_token . '/sendMessage';
            $fields = [
                'chat_id' => $this->chat_id,
                'parse_mode' => 'HTML',
                'text' =>
                '<b>Ошибка в настройке форм</b>'.PHP_EOL.
                'Нет E-mail клиента: '.$_SERVER['HTTP_HOST'].PHP_EOL.
                'Указанные E-mail: '.$emails.PHP_EOL.
                'Расположение модуля форм: '.IEXFORM_DIR,
            ];

            $ch = curl_init();
            $opts = array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST => 1,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_POSTFIELDS => $fields
            );

            if($this->proxy_data !== ''){
                $opts[CURLOPT_PROXY] = $this->proxy_data;
            }

            curl_setopt_array($ch, $opts);

            $this->result = curl_exec($ch);
        }

        return true;

    }
}