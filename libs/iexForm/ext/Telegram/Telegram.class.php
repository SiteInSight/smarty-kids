<?
include 'TelegramAPI.class.php';

/**
 * Class Telegram
 *
 * Данные формы будут отправлены ботом
 */
class Telegram extends ExtensionBase {

    public $version = '2.0';

    public $priority = 600;

    /**
     * Экземпляр класса TelegramAPI
     *
     * @var TelegramAPI $telegram
     */
    public $telegram;

    /**
     * Список имен полей, которые будут добавлены в сообщение
     * @var string[] $formFields необязательно
     */
    public $formFields = [];

    /**
     * Блок текста, добавляемый к сообщению ДО содержимого $this->$formFields.
     * Может содержать управляющие символы типа "\n".
     * @var string $textBefore необязательное
     */
    public $textBefore = '';

    /**
     * Блок текста, добавляемый к сообщению ПОСЛЕ содержимого $this->$formFields.
     * Может содержать управляющие символы типа "\n".
     * @var string $textAfter необязательное
     */
    public $textAfter = '';

    /**
     * Токен полученный от "@BotFather"
     * @var string $botToken обязательный
     */
    public $botToken = '';

    /**
     * Числовой id чата в Telegram, к которому есть доступ у бота
     * @var integer $chatId обязательный
     */
    public $chatId = 0;

    /**
     * Строка вида: "socks5://bob:marley@localhost:12345"
     * Если пусто - прокси не используется
     * @var string $proxy необязательно
     */
    public $proxy = '';

    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);
    }

    public function onSuccess() {
        parent::onSuccess();

        $this->telegram = new TelegramAPI();

        $text = $this->textBefore;

        foreach ($this->formFields as $name) {
            $field = $this->getField($name, '');
            if ( $field !== false && $field->value ) {
                $text .= $field->header . ': ' . $field->value . "\n";
            }
        }
        $text .= $this->textAfter;

        $this->telegram->message = $text;
        if ( $this->botToken ) {
            $this->telegram->botToken = $this->botToken;
        }
        if ( $this->chatId ) {
            $this->telegram->chatId = $this->chatId;
        }
        if ( $this->proxy ) {
            $this->telegram->proxy = $this->proxy;
        }

        $this->telegram->sendMessage();

        //$this->core->log('Telegram: Опции переданные CURL:');
        //$this->core->log($this->telegram->curlOptions);
        $this->core->log('Telegram: Ответ сервера');
        try{
            $this->core->log( $this->telegram->result );
        } catch(Exception $e){
            $this->core->log( $e->getMessage() );
        }

    }
}