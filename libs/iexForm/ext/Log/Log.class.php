<?php
/**
 * Class Log
 *
 * Логгирование в текстовый файл
 */
class Log extends ExtensionBase {

    public $version = '1.1';

    public $priority = 100;

    /**
     * Фильтр запросов для записи в лог
     *
     * @var array
     * [
     *     'pformid' => ['inline-calc', 'popup-calc'],  // записывать только формы с указанными pfromid
     *     'submitOnly' => false,                       // записывать все попытки отправки формы, включая неуспешные (исключить запросы HTML-кода формы)
     *     'successOnly' => false,                      // записывать только успешные попытки отправки формы
     *     'errorsOnly' => false,                       // записывать только неуспешные попытки отправки формы, включая СПАМ и истечение сессии в многошаговых формах
     * ]
     */
    public $filter = [];

    /**
     * @var string Папка для хранения лог-файлов от корня сайта
     */
    public $dir = '/iexform-logs/';

    /**
     * @var int Максимальный размер лог-файла в байтах, при превышении которого будет создан новый лог-файл, а старый переименован
     */
    public $fileMaxSize = '4m';

    private $fileFullName = '#-log.txt'; // полное имя генерим в конструкторе (вместо решетки)

    public $mkdirMode = 0755;

    public $buffer = '';

    private $active = true;

    public function __construct(IexForm $core, array $params = array()) {
        parent::__construct($core, $params);

        $this->dir = $this->core->documentRoot . $this->dir;
        $this->fileFullName = $this->dir . str_replace('#', $this->core->configName, $this->fileFullName);
        $this->fileMaxSize = $this->core->sizeInBytes($this->fileMaxSize);
        $this->filter = is_array($this->filter) ? $this->filter : [];

        $this->before();
    }

    public function beforeParseTemplate() {
        parent::beforeParseTemplate();
    }

    public function beforeShow() {
        parent::beforeShow();
    }

    public function beforeValidate() {
        parent::beforeValidate();
    }

    public function afterValidate($isValid) {
        parent::afterValidate($isValid);
    }
    
    public function onSuccess() {
        parent::onSuccess();
    }

    public function onFinish() {
        parent::onFinish();

        $this->filter();
        if ( $this->active ) {
            $this->after();
            $this->save();
        }
    }

    public function filter(){
        if (
            $this->active &&
            isset($this->filter['pformid']) &&
            is_array($this->filter['pformid']) &&
            !in_array($this->core->pformid, $this->filter['pformid'], true)
        ) {
            $this->active = false;
        }

        if (
            $this->active &&
            isset($this->filter['submitOnly']) &&
            $this->filter['submitOnly'] &&
            !$this->core->submited
        ) {
            $this->active = false;
        }

        if (
            $this->active &&
            isset($this->filter['successOnly']) &&
            $this->filter['successOnly'] &&
            (
                !$this->core->submited ||
                !$this->core->state['isValid'] ||
                $this->core->isSpam
            )
        ) {
            $this->active = false;
        }

        if (
            $this->active &&
            isset($this->filter['errorsOnly']) &&
            $this->filter['errorsOnly'] &&
            (
                !$this->core->submited ||
                (
                    $this->core->state['isValid'] &&
                    !$this->core->isSpam
                )
            )
        ) {
            $this->active = false;
        }

    }

    public function before(){
        $this->add('Pformid: '.$this->core->pformid);
        $this->add('Uid'.($this->core->isSpam ? ' (невалидный)' : '').': '.$this->core->uid);
        $this->add('Ts: '.$this->core->ts);
        $this->add('Request: ' . $_SERVER['REQUEST_URI']);
        $this->add('Referer: ' . $_SERVER['HTTP_REFERER']);
        $this->add('User agent: ' . $_SERVER['HTTP_USER_AGENT']);
        $this->add('IP: ' . $_SERVER['REMOTE_ADDR']);
        $this->add('Тип запроса/ответа: '.( $this->core->submited ? 'Submit формы / JSON' : 'HTML-код формы'));

        if ($_POST) {
            $this->add('$_POST:');
            $this->add($_POST);
        } else {
            $this->add('$_POST: ()');
        }

        if ($_FILES) {
            $this->add('$_FILES:');
            $this->add($_FILES);
        } else {
            $this->add('$_FILES: ()');
        }
    }

    public function after(){
        if ( $this->core->submited ) {
            if ( $this->core->isSpam ) {
                $this->add('Статус: СПАМ');
            } else {
                $this->add('Статус: '.( $this->core->state['isValid'] ? 'OK' : 'ERR' ));
            }
            $this->add( $this->core->state );
            $this->add('JSON:');
            $this->add($this->core->arJson);
        }

        if (
            $this->core->submited &&
            $this->core->state['isValid'] &&
            (!$this->core->multistep || ($this->core->multistep && $this->core->step == $this->core->stepsCnt))
        ) {
            $this->add('Отвалидированные поля:');
            $this->add($this->getFields());
        }
    }
    
    public function add($content = '') {
        $this->buffer .= is_string($content) ? $content . "\n" : print_r($content, true);
    }

    private function save($text = '') {
        if ( !$this->buffer ) return;

        if ( !file_exists($this->dir) ) {
            /** @noinspection MkdirRaceConditionInspection */
            mkdir($this->dir, $this->mkdirMode, true);
        }
        if (
            file_exists($this->fileFullName) &&
            filesize($this->fileFullName) > $this->fileMaxSize
        ) {
            rename($this->fileFullName, $this->fileFullName.'-old-'.date('Y-m-d-H-i-s-').$this->microSecs());
        }

        $txt = date('Y.m.d H:i:s:') . $this->microSecs() . "\n";
        $txt .= ($text ? $text : $this->buffer) . "\n\n";

        $txt = preg_replace("#Array\n\(#", '(', $txt);
        $txt = preg_replace('#=\> Array#', '=>', $txt);
        $txt = preg_replace('#\)\n\n(\s+?)\[#', ")\n$1[", $txt);

        $file = fopen($this->fileFullName, "ab");
        fwrite($file, $txt);
        fclose($file);
        $this->buffer = '';
    }

    public function microSecs($precision = 5, $fracOnly = true) {
        $time = round(fmod(microtime(true), 1), $precision); // float 0.12345
        if ($fracOnly) {
            $time = str_replace('0.', '', $time);
        }
        return $time;
    }

    public function toFile($var = null, $params = []) {
        if (!(isset($params['path']) && $params['path'])) $params['path'] = '/iexform-dump.txt';
        $path = $this->core->documentRoot . $params['path'];
        if (!isset($params['timestamp'])) $params['timestamp'] = true;
        if (!isset($params['emptylines'])) $params['emptylines'] = true;

        $txt = $params['timestamp'] ? (date('Y.m.d H:i:s:') . $this->microSecs() . "\n") : '';
        $txt .= is_string($var) ? $var . "\n" : print_r($var, true);
        $txt .= $params['emptylines'] ? "\n\n" : '';

        $file = fopen($path, "ab");
        fwrite($file, $txt);
        fclose($file);
    }
}