<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

spl_autoload_register(function ($class) {
    if ( $class === 'PHPMailer\PHPMailer\PHPMailer' ) {
        require __DIR__.'/PHPMailer/src/Exception.php';
        require __DIR__.'/PHPMailer/src/PHPMailer.php';
        require __DIR__.'/PHPMailer/src/SMTP.php';
    }
    if ( $class === 'TelegramAPI' ) {
        require IEXFORM_DIR.'/ext/Telegram/TelegramAPI.class.php';
    }
});

/**
 * Class Mail
 *
 * Отправка по емэйлу данных форм.
 * Используется встроенная в PHP функцию `mail()` (по-умолчанию) или PHPMailer
 *
 * @see Bitrix
 * @see Utm
 */
class Mail extends ExtensionBase {

    public $version = '3.0';

    public $priority = 700;

    /**
     * E-mail получателя. Несколько емэйлов - через запятую, false - не отправлять емэйл
     * @var string|boolean $to обязательный
     */
    public $to;

    /**
     * E-mail отправителя в строгом формате. По-умолчанию: site@%HTTP_HOST% <site@%HTTP_HOST%>
     * @var string|boolean $from необязательный
     */
    public $from;

    /**
     * Получается из $this->from
     * @var string
     */
    public $fromName;

    /**
     * Получается из $this->from
     * @var string
     */
    public $fromEmail;

    /**
     * E-mail получателя ответного сообщения в одном из двух форматов
     * @var string|array $replyTo необязательный
     */
    public $replyTo;

    /**
     * Получается из $this->replyTo
     * @var string
     */
    public $replyToName;

    /**
     * Получается из $this->replyTo
     * @var string
     */
    public $replyToEmail;

    /**
     * HTML, вставляемый в письмо до данных формы
     * @var string $htmlBefore необязательный
     */
    public $htmlBefore;

    /**
     * HTML, вставляемый в письмо после данных формы
     * @var string $htmlAfter необязательный
     */
    public $htmlAfter;

    /**
     * Тема письма
     * @var string $subject необязательный
     */
    public $subject = 'Заполнена форма';

    /**
     * Дописывать ли к теме значение data-pform-position текущей формы.
     * Пример: <b>Заполнена форма: Обратный звонок
     * @var string $autoSubject необязательный
     */
    public $autoSubject = true;

    /**
     * Префикс для кастомных заголовков
     * @var string
     */
    public $customHeader = 'X-Iexform-Mode: work';

    /**
     * Добавлять ли в JSON-ответ формы поле mailReport
     * c отчетом об успешности отправки в виде объекта
     * {
     *     'mail_1@domen.ru' => true,
     *     'mail_2@domen.ru' => false,
     * }     *
     * Важно не забывайте выключать этот флаг, во избежание засвета емэйлов!!!     *
     * @var bool
     */
    public $jsonReport = false;

    /**
     * Если сайт боевой ($_SERVER['HTTP_HOST'] не содержит *.bex.su) проверять,
     * что в списке емэйлов не одни только тестовые адреса (заканчивающиеся на *@iex.su)
     *
     * @var bool $checkProduction
     */
    public $checkProductionEmails = true;

    /**
     * Настройки для SMTP-сервера     *
     * @var bool
     */
    public $smtp = false;

    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);

        if ( $this->from ) {
            $exploded = $this->addrExplode($this->from);
            $this->fromName = $this->encodeCyrilic($exploded['name']);
            $this->fromEmail = $exploded['email'];
        } else {
            $this->fromName = $_SERVER['SERVER_NAME'];
            $this->fromEmail = 'site@'.$_SERVER['SERVER_NAME'];
        }

        $this->htmlBefore = $this->htmlBefore !== null ? $this->htmlBefore : '';
        $this->htmlAfter = $this->htmlAfter !== null ? $this->htmlAfter : '';
        $this->jsonReport = is_bool($this->jsonReport) ? $this->jsonReport : false;

        if (
            !is_array($this->smtp) ||
            !isset($this->smtp['server'], $this->smtp['port'], $this->smtp['login'], $this->smtp['password'])
        ) {
            $this->smtp = false;
        }
    }

    /**
     * Возвращает строку с заголовками
     * @return string
     */
    public function getHeaders() {
        $headers = array(
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->addrImplode(['name'=>$this->fromName, 'email'=>$this->fromEmail]),
            'Reply-To: ' . $this->addrImplode(['name'=>$this->replyToName, 'email'=>$this->replyToEmail]),
            $this->customHeader
        );
        return implode("\r\n", $headers)."\r\n";
    }

    public function isProdSite(){
        return preg_match('/\.bex\.su$/', $_SERVER['HTTP_HOST']) === 0;
    }

    public function hasProdEmails(){
        $arEmails = explode(',', $this->to);
        $has = false;
        foreach ($arEmails as $email) {
            $email = trim($email);
            if( preg_match('/@iex.su$/', $email) === 0 ) {
                $has = true;
                break;
            }
        }
        return $has;
    }

    public function notifyToTelegram(){
        $telegram = new TelegramAPI();

        $position = $this->getField('position', 'AdditionalFields');
        if ( $position && $position->value ) {
            $position = 'Форма: '.$position->value.PHP_EOL;
        }

        $telegram->message =
            '<b>Проверьте емэйлы клиента</b>'.PHP_EOL.
            'Страница: '.$_SERVER['HTTP_REFERER'].PHP_EOL.
            $position.
            'Емэйлы: '.$this->to.PHP_EOL.
            'Папка модуля: '.IEXFORM_DIR;

        $telegram->sendMessage();

        $this->core->log('Mail: Результат уведомления в Telegram:');
        $this->core->log($telegram->result);
    }

    private function checkProduction(){
        if (
            $this->checkProductionEmails &&
            $this->isProdSite() &&
            !$this->hasProdEmails()
        ) {
            $this->notifyToTelegram();
        }
    }

    public function onSuccess() {
        parent::onSuccess();

        $this->checkProduction();

        $report = $this->send();

        $this->core->log('Результаты отправки емэйлов:');
        $this->core->log($report);

        if ($this->jsonReport) {
            $this->core->arJson['mailReport'] = $report;
        }
    }

    /**
     * Возвращает E-mail получателя (через запятую), либо ложь – если отправление
     * получателю не включено (например, используется только генерация HTML письма)
     *
     * Используется сторонними расширениями
     *
     * @return bool|string
     */
    public function getToEmail() {
        return $this->to;
    }

    public function isEmail($text) {
        return $this->core->validators['isEmail']($text);
    }

    public function encodeCyrilic($content = '') {
        if (!empty($content)) {
            $content = '=?UTF-8?B?' . base64_encode($content) . '?='; // =?UTF-8?Q? взято c Gmail.com
        }
        return trim($content);
    }

    public function addrExplode(string $addr = ''): array {
        if (!empty($addr)) {
            $email = preg_replace('/^.*?<(.+?)>.*$/', '$1', $addr);
            $name = trim(preg_replace('/^(.*?)<.+?>.*$/', '$1', $addr));
            $addr = array('name' => $name, 'email' => $email);
        }
        return $addr;
    }

    public function addrImplode(array $addr = array()): string {
        if ( $addr['name'] ) {
            $imploded = $addr['name'] . ' <' . $addr['email'] . '>';
        } else {
            $imploded = $addr['email'];
        }
        return $imploded;
    }

    public function prepareReplyTo() {
        if ( $this->replyTo ) {
            if (is_string($this->replyTo)) {
                $exploded = $this->addrExplode($this->replyTo);
                $this->replyToName = $this->encodeCyrilic($exploded['name']);
                $this->replyToEmail = $exploded['email'];
            } elseif (
                is_array($this->replyTo) &&
                isset($this->replyTo['name'], $this->replyTo['email']) &&
                $this->isEmail($this->getField($this->replyTo['email'], '')->value)
            ) {
                $this->replyToName = $this->encodeCyrilic($this->getField($this->replyTo['name'], '')->value);
                $this->replyToEmail = $this->getField($this->replyTo['email'], '')->value;
            }
        }
        if ( !$this->replyToName ) {
            $this->replyToName = $this->fromName;
        }
        if ( !$this->replyToEmail ) {
            $this->replyToEmail = $this->fromEmail;
        }
    }

    public function getUtmsHtml() {
        $html = '';
        $utm_ext = $this->ext('Utm');

        if ($utm_ext === false) {
            return $html;
        }

        /**
         * @var Utm $utm_ext
         */

        if (!count($utm_ext->getGetParamsFiltered())) {
            return $html;
        }
        $html = '<hr><br>';

        foreach ($utm_ext->getGetParamsFiltered() as $param => $value) {
            $title = $utm_ext->getGetParamsTitles($param);
            $html .= '<strong>' . $title . ':</strong> ' . $value . "<br>\n";
        }

        return $html;
    }

    /**
     * Возвращает подготовленную разметку письма,
     * может быть использовано сторонними плагинами
     * @return string HTML код письма
     */
    public function getMailHtml() {
        $mailerHtml = '';

        foreach ($this->getFields() as $field) {
            $name = $field->name;
            if (
                $field->notsend ||
                empty($field->value)
            ) { continue; }

            $mailerHtml .= '<strong>' . $field->header . ':</strong> ';

            if ($field['type'] === 'file') {
                $url = 'http://' . $_SERVER["SERVER_NAME"] . $field->value;
                $mailerHtml .= '<a href="' . $url . '" target="_blank">' . $field->name . '</a>';
            } elseif ($name === 'iexurl') {
                $mailerHtml .= '<a href="' . $field->value . '" target="_blank">' . $field->value . '</a>';
            } else {
                $mailerHtml .= $field->value;
            }

            $mailerHtml .= "<br/>\n";
            $mailerHtml .= $name === 'additionalfields_url' ? '<hr>' . "\n" : '';
        }

        $mailerHtml .= $this->getUtmsHtml();
        $mailerHtml .= $this->ext('Bitrix') ? $this->ext('Bitrix')->getComment() : '';

        //$mailerHtml = $mailerHtml ? $mailerHtml : '<p>iexForm: Похоже, форма не содержит полей.</p>';
        $mailerHtml = $this->htmlBefore . $mailerHtml . $this->htmlAfter;

        return $mailerHtml;
    }

    public function sendByMailer($to, $content) {
        $mailer = new PHPMailer(true);                              // Passing `true` enables exceptions

        try {

            //Server
            //$mailer->SMTPDebug = 2;                                 // Enable verbose debug output
            $mailer->isSMTP();                                      // Set mailer to use SMTP
            $mailer->SMTPAuth = true;                               // Enable SMTP authentication
            $mailer->SMTPSecure = isset($this->smtp['SMTPSecure']) ? $this->smtp['SMTPSecure'] : 'ssl'; // Enable TLS encryption, `ssl` also accepted
            $mailer->Host = $this->smtp['server'];                  // Specify main and backup SMTP servers
            $mailer->Port = $this->smtp['port'];                    // TCP port to connect to
            $mailer->Username = $this->smtp['login'];               // SMTP username
            $mailer->Password = $this->smtp['password'];            // SMTP password

            //Recipients
            $mailer->addAddress(trim($to));
            $mailer->setFrom($this->fromEmail, $this->fromName);
            //$mailer->addAddress('joe@example.net', 'Joe User');     // Add a recipient
            //$mailer->addAddress('ellen@example.com');               // Name is optional
            $mailer->addReplyTo($this->replyToEmail, $this->replyToName);
            //$mailer->addCC('cc@example.com');
            //$mailer->addBCC('bcc@example.com');

            //Attachments
            //$mailer->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mailer->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

            //Headers
            $mailer->addCustomHeader($this->customHeader);

            //Content
            $mailer->isHTML(true);                                     // Set email format to HTML
            $mailer->CharSet = 'utf-8';                                // Set email format to HTML
            $mailer->Subject = $this->subject;
            $mailer->Body    = $content;
            //$mailer->AltBody = 'This is the body in plain text for non-HTML mail clients';

            return $mailer->send();

        } catch (Exception $e) {

            return 'PHPMailer error: ' . $mailer->ErrorInfo;

        }
    }

    public function send() {
        $this->prepareReplyTo();

        if ($this->autoSubject && !empty($this->getField('position', 'AdditionalFields')->value)) {
            $this->subject .= ': ' . $this->getField('position', 'AdditionalFields')->value;
        }
        $this->subject = $this->encodeCyrilic($this->subject);

        $report = [
            'sender' => $this->smtp ? 'PHPMailer' : 'mail()',
            'results' => []
        ];
        $arTo = explode(',', $this->to);

        $headers = $this->getHeaders();
        $content = $this->getMailHtml();

        foreach ($arTo as $to) {
            $to = trim($to);
            if ( $this->smtp ) {
                $result = $this->sendByMailer($to, $content);
            } else {
                $result = mail($to, $this->subject, $content, $headers); // '-f'.$from
            }
            $report['results'][$to] = $result;
        }

        return $report;
    }
}