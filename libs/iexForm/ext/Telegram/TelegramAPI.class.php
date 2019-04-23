<?php
/**
 * TelegramAPI class
 *
 * Базовый функционал для отправки сообщений в Telegram
 *
 * https://core.telegram.org/bots/api
 */
class TelegramAPI {

    /**
     * Поддерживаемые методы
     *
     * @var array
     */
    public $methods = ['sendMessage', 'getUpdates', 'deleteWebhook'];
    
    /**
     * Токен полученный от BotFather
     * 604114644:AAGa2EAyK-OGl0ko1GKsppczszXbgk9eQQM @iexform_bot (для канала iexFormReport от Павлюкова Ильи)
     * 777361539:AAFdEoFNzvNn_DWm0Bw5vmaqnyTucZ0ro9A @iexForm2019Bot (для группы iexForm2019 от Дзвонковского Ильи)
     *
     * @var string $botToken обязательный
     */
    public $botToken = '777361539:AAFdEoFNzvNn_DWm0Bw5vmaqnyTucZ0ro9A'; // IexFormBot

    /**
     * Числовой id чата в Telegram, к которому есть доступ у бота
     * -1001347383478 IexForm-канал от Павлюкова Ильи
     * -1001455307575 группа iexForm2019 c ботом @iexForm2019Bot     *
     * 
     * @var integer $chatId обязательный
     */
    public $chatId = -1001455307575;

    /**
     * Строка вида: "socks5://bob:marley@localhost:12345"
     * Если пусто - прокси не используется
     * 
     * @var string $proxy необязательно
     */
    public $proxy = 'socks5://iexproxy:iexproxypass@vpn2.alexxwiz.ru:1080';

    /**
     * Сообщение для отправки.
     * Может содержать управляющие символы типа "\n"
     * а так же базовую HTML-верстку:
     *
     * <b>bold</b>, <strong>bold</strong>
     * <i>italic</i>, <em>italic</em>
     * <a href="URL">inline URL</a>
     * <code>inline fixed-width code</code>
     * <pre>pre-formatted fixed-width code block</pre>
     * 
     * @var string $message
     */
    public $message = '';

    /**
     * Ответ сервера Telegram
     * 
     * @var array $result
     */
    public $result = [];

    /**
     * Для просмотра-логгирования опций CURL снаружи
     *
     * @var array
     */
    public $curlOptions = [];

    public function __construct(){
    }

    private function runMethod($method = ''){

        if ( !in_array($method, $this->methods, true) ) {
            return false;
        }

        $this->curlOptions = [
            CURLOPT_URL => 'https://api.telegram.org/bot' . $this->botToken . '/' . $method,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 10,
        ];
        if ( $this->proxy !== '' ) {
            $this->curlOptions[CURLOPT_PROXY] = $this->proxy;
        }

        $postFields = [
            'chat_id' => $this->chatId
        ];

        if ( $method === 'sendMessage' ) {
            $postFields['parse_mode'] = 'HTML';
            $postFields['text'] = $this->message;
        }

        $this->curlOptions[CURLOPT_POSTFIELDS] = $postFields;

        $ch = curl_init();
        curl_setopt_array($ch, $this->curlOptions);
        $result = curl_exec($ch);

        $this->result = [];
        try {
            $this->result = json_decode($result, true);
        } catch (Exception $e) {
        }

        return true;
    }
    
    public function sendMessage(){
        return $this->runMethod('sendMessage');
    }

    public function getUpdates(){
        return $this->runMethod('getUpdates');
    }

    public function deleteWebhook(){
        return $this->runMethod('deleteWebhook');
    }
}